<?php

namespace App\Controller;

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

#[Route('/therapists', name: 'app_therapist_')]
class TherapistController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, UserRepository $userRepository): Response
    {
        $location = $request->query->get('location');
        $search = $request->query->get('search');

        $therapists = $userRepository->findTherapists($location, $search);

        return $this->render('therapist/list.html.twig', [
            'therapists' => $therapists,
            'location' => $location,
            'search' => $search,
        ]);
    }

    #[Route('/{slug}', name: 'show', methods: ['GET', 'POST'])]
    public function show(
        Request $request,
        #[MapEntity(mapping: ['slug' => 'slug'])] User $therapist,
        AvailabilityRepository $availabilityRepository,
        AppointmentRepository $appointmentRepository,
        EntityManagerInterface $entityManager,
        EmailService $emailService
    ): Response {
        if (!in_array('ROLE_THERAPIST', $therapist->getRoles())) {
            throw $this->createNotFoundException('Therapist not found');
        }

        // Wybór dnia (domyślnie dziś)
        $date = $request->query->get('date') ? new \DateTime($request->query->get('date')) : new \DateTime();
        $availableSlots = $availabilityRepository->findAvailableSlots($therapist, $date);

        $success = false;
        $error = null;
        if ($request->isMethod('POST') && $this->isGranted('ROLE_PATIENT')) {
            $dateStr = $request->request->get('date');
            $hourStr = $request->request->get('hour');
            if (!$dateStr || !$hourStr) {
                $error = 'Please select date and hour.';
            } else {
                $dateObj = \DateTime::createFromFormat('Y-m-d H:i', $dateStr . ' ' . $hourStr);
                if (!$dateObj) {
                    $error = 'Invalid date or hour.';
                } else {
                    // Sprawdź, czy slot nie jest już zajęty
                    $existing = $appointmentRepository->findOneBy([
                        'therapist' => $therapist,
                        'startTime' => $dateObj
                    ]);
                    if ($existing) {
                        $error = 'This slot is already booked.';
                    } else {
                        $appointment = new \App\Entity\Appointment();
                        $appointment->setTherapist($therapist);
                        $appointment->setClient($this->getUser());
                        $appointment->setStartTime($dateObj);
                        $appointment->setEndTime((clone $dateObj)->modify('+1 hour'));
                        $appointment->setStatus('scheduled');
                        $appointment->setPrice($therapist->getHourlyRate() ?? 0);
                        $entityManager->persist($appointment);
                        $entityManager->flush();
                        $emailService->sendAppointmentConfirmation($appointment);
                        $success = true;
                    }
                }
            }
        }

        return $this->render('therapist/show.html.twig', [
            'therapist' => $therapist,
            'date' => $date,
            'available_slots' => $availableSlots,
            'success' => $success,
            'error' => $error,
        ]);
    }

    #[Route('/{slug}/available-hours', name: 'app_therapist_available_hours', methods: ['GET'])]
    public function availableHours(
        User $therapist,
        Request $request,
        AvailabilityRepository $availabilityRepository,
        AppointmentRepository $appointmentRepository
    ): Response {
        $dateStr = $request->query->get('date');
        if (!$dateStr) {
            return $this->json(['error' => 'Missing date'], 400);
        }
        $date = \DateTime::createFromFormat('Y-m-d', $dateStr);
        if (!$date) {
            return $this->json(['error' => 'Invalid date'], 400);
        }
        // Pobierz dostępność terapeuty na ten dzień tygodnia
        $dayOfWeek = $date->format('N'); // 1=pon, 7=nd
        $availabilities = $availabilityRepository->findBy([
            'therapist' => $therapist,
            'dayOfWeek' => $dayOfWeek,
            'isAvailable' => true,
        ]);
        // Wyklucz wyłączone daty
        $availabilities = array_filter($availabilities, function($a) use ($date) {
            return !$a->isExcludedDate($date);
        });
        if (empty($availabilities)) {
            return $this->json([]);
        }
        // Zbierz zajęte sloty (Appointment)
        $appointments = $appointmentRepository->findAvailableSlots($therapist, $date);
        $taken = [];
        foreach ($appointments as $app) {
            $taken[] = $app->getStartTime()->format('H:i');
        }
        // Generuj sloty co 30 min w ramach dostępności
        $slots = [];
        foreach ($availabilities as $a) {
            $start = (clone $date)->setTime((int)$a->getStartHour()->format('H'), (int)$a->getStartHour()->format('i'));
            $end = (clone $date)->setTime((int)$a->getEndHour()->format('H'), (int)$a->getEndHour()->format('i'));
            while ($start < $end) {
                $slotStr = $start->format('H:i');
                if (!in_array($slotStr, $taken)) {
                    $slots[] = $slotStr;
                }
                $start->modify('+30 minutes');
            }
        }
        return $this->json($slots);
    }
}
