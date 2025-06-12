<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Repository\AvailabilityRepository;
use App\Repository\UserRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use App\Entity\AppointmentStatus;
use App\Dto\Therapist\BookAppointmentDto;
use App\Manager\AppointmentManager;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Service\AvailabilityService;
use App\Service\TherapistCacheService;

#[Route('/therapists', name: 'app_therapist_')]
class TherapistController extends AbstractController
{
    public function __construct(private AppointmentManager $appointmentManager) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, UserRepository $userRepository, TherapistCacheService $therapistCacheService): Response
    {
        $location = $request->query->get('location');
        $search = $request->query->get('search');
        $sort = $request->query->get('sort', 'lastName_asc');
        $page = max(1, (int)$request->query->get('page', 1));
        $perPage = 6;

        $filters = [
            'location' => $location,
            'search' => $search,
            'sort' => $sort,
            'page' => $page,
            'perPage' => $perPage,
        ];

        $result = $therapistCacheService->getTherapistList($filters, function () use ($userRepository, $location, $search, $sort, $page, $perPage) {
            return $userRepository->findTherapists($location, $search, $sort, $page, $perPage);
        });
        $therapists = $result['results'];
        $total = $result['total'];
        $totalPages = (int) ceil($total / $perPage);

        // Przygotuj query params do paginacji
        $query = $request->query->all();
        unset($query['page']);

        return $this->render('app/therapist/list.html.twig', [
            'therapists' => $therapists,
            'location' => $location,
            'search' => $search,
            'sort' => $sort,
            'page' => $page,
            'total_pages' => $totalPages,
            'query' => $query,
        ]);
    }

    #[Route('/{slug}', name: 'show', methods: ['GET', 'POST'])]
    public function show(
        Request $request,
        #[MapEntity(mapping: ['slug' => 'slug'])] User $therapist,
        AvailabilityRepository $availabilityRepository,
        AppointmentRepository $appointmentRepository,
        EntityManagerInterface $entityManager,
        EmailService $emailService,
        ValidatorInterface $validator,
        TherapistCacheService $therapistCacheService
    ): Response {
        if (!in_array('ROLE_THERAPIST', $therapist->getRoles())) {
            throw $this->createNotFoundException('Therapist not found');
        }

        $date = $request->query->get('date') ? new \DateTime($request->query->get('date')) : new \DateTime();
        $availableSlots = $availabilityRepository->findAvailableSlots($therapist, $date);

        $success = false;
        $error = null;

        if ($request->isMethod('POST') && $this->isGranted('ROLE_PATIENT')) {
            $dto = new BookAppointmentDto();
            $dto->date = $request->request->get('date');
            $dto->hour = $request->request->get('hour');
            $errors = $validator->validate($dto);
            if (count($errors) > 0) {
                $error = (string) $errors;
            } else {
                $result = $this->appointmentManager->bookAppointmentForTherapistPage($dto, $therapist, $this->getUser());
                $success = $result['success'];
                $error = $result['error'];
                if ($result['success']) {
                    $emailService->sendAppointmentConfirmation($result['appointment']);
                }
            }
        }

        $therapistData = $therapistCacheService->getTherapistProfile($therapist->getSlug(), function () use ($therapist) {
            return $therapist;
        });

        return $this->render('app/therapist/show.html.twig', [
            'therapist' => $therapistData,
            'date' => $date,
            'available_slots' => $availableSlots,
            'success' => $success,
            'error' => $error,
        ]);
    }

    #[Route('/{slug}/available-hours', name: 'available_hours', methods: ['GET'])]
    public function availableHours(
        #[MapEntity(mapping: ['slug' => 'slug'])] User $therapist,
        Request $request,
        AvailabilityService $availabilityService
    ): Response {
        $dateStr = $request->query->get('date');
        if (!$dateStr) {
            return $this->json(['error' => 'Missing date'], 400);
        }
        $date = \DateTime::createFromFormat('Y-m-d', $dateStr);
        if (!$date) {
            return $this->json(['error' => 'Invalid date'], 400);
        }
        $slots = $availabilityService->getAvailableHours($therapist, $date);
        return $this->json($slots);
    }
}
