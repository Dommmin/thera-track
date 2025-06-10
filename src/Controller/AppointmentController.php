<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Repository\AvailabilityRepository;
use App\Service\EmailService;
use App\Service\GoogleCalendarService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/appointments')]
class AppointmentController extends AbstractController
{
    public function __construct(
        private EmailService $emailService,
        private GoogleCalendarService $googleCalendarService
    ) {
    }

    #[Route('/', name: 'app_appointment_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(
        AppointmentRepository $appointmentRepository,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        $upcomingAppointments = $appointmentRepository->findUpcomingAppointments($user);
        $pastAppointments = $appointmentRepository->findPastAppointments($user);

        // Get all therapists
        $therapists = $entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_THERAPIST%')
            ->getQuery()
            ->getResult();

        return $this->render('appointment/index.html.twig', [
            'upcoming_appointments' => $upcomingAppointments,
            'past_appointments' => $pastAppointments,
            'therapists' => $therapists,
        ]);
    }

    #[Route('/new/{therapist}', name: 'app_appointment_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        User $therapist,
        AvailabilityRepository $availabilityRepository,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        if ($user === $therapist) {
            throw $this->createAccessDeniedException('You cannot book an appointment with yourself.');
        }

        if (!$therapist->isTherapist()) {
            throw $this->createAccessDeniedException('This user is not a therapist.');
        }

        $date = new \DateTime($request->query->get('date', 'now'));
        $availableSlots = $availabilityRepository->findAvailableSlots($therapist, $date);

        if ($request->isMethod('POST')) {
            $startTime = new \DateTime($request->request->get('start_time'));
            $endTime = (clone $startTime)->modify('+1 hour');

            $appointment = new Appointment();
            $appointment->setTherapist($therapist);
            $appointment->setClient($user);
            $appointment->setStartTime($startTime);
            $appointment->setEndTime($endTime);
            $appointment->setPrice($therapist->getHourlyRate());
            $appointment->setStatus('scheduled');

            $entityManager->persist($appointment);
            $entityManager->flush();

            $this->emailService->sendAppointmentConfirmation($appointment);
            $this->googleCalendarService->addAppointment($appointment);

            $this->addFlash('success', 'Appointment booked successfully!');
            return $this->redirectToRoute('app_appointment_index');
        }

        return $this->render('appointment/new.html.twig', [
            'therapist' => $therapist,
            'available_slots' => $availableSlots,
            'date' => $date,
        ]);
    }

    #[Route('/{id}', name: 'app_appointment_show', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Appointment $appointment): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if ($user !== $appointment->getTherapist() && $user !== $appointment->getClient()) {
            throw $this->createAccessDeniedException('You do not have access to this appointment.');
        }

        return $this->render('appointment/show.html.twig', [
            'appointment' => $appointment,
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_appointment_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(
        Request $request,
        Appointment $appointment,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        if ($user !== $appointment->getTherapist() && $user !== $appointment->getClient()) {
            throw $this->createAccessDeniedException('You cannot cancel this appointment.');
        }

        if ($this->isCsrfTokenValid('cancel'.$appointment->getId(), $request->request->get('_token'))) {
            $appointment->setStatus('cancelled');
            $entityManager->flush();

            $this->emailService->sendAppointmentCancellation($appointment);
            $this->googleCalendarService->updateAppointment($appointment);

            $this->addFlash('success', 'Appointment cancelled successfully.');
        }

        return $this->redirectToRoute('app_appointment_index');
    }
} 