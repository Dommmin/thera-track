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
            $slotId = $request->request->get('slot_id');
            $slot = null;
            foreach ($availableSlots as $s) {
                if ($s->getId() == $slotId) {
                    $slot = $s;
                    break;
                }
            }
            if ($slot) {
                // Sprawdź, czy slot nie jest już zajęty
                $existing = $appointmentRepository->findOneBy([
                    'therapist' => $therapist,
                    'startTime' => (clone $date)->setTime((int)$slot->getStartHour()->format('H'), (int)$slot->getStartHour()->format('i'))
                ]);
                if ($existing) {
                    $error = 'This slot is already booked.';
                } else {
                    $appointment = new \App\Entity\Appointment();
                    $appointment->setTherapist($therapist);
                    $appointment->setClient($this->getUser());
                    $start = (clone $date)->setTime((int)$slot->getStartHour()->format('H'), (int)$slot->getStartHour()->format('i'));
                    $end = (clone $date)->setTime((int)$slot->getEndHour()->format('H'), (int)$slot->getEndHour()->format('i'));
                    $appointment->setStartTime($start);
                    $appointment->setEndTime($end);
                    $appointment->setStatus('scheduled');
                    $appointment->setPrice($therapist->getHourlyRate() ?? 0);
                    $entityManager->persist($appointment);
                    $entityManager->flush();
                    // Powiadomienia e-mail
                    $emailService->sendAppointmentConfirmation($appointment);
                    $success = true;
                }
            } else {
                $error = 'Invalid slot.';
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
}
