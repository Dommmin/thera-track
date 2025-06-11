<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\User;
use App\Entity\AppointmentStatus;
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
use App\Dto\Appointment\CreateAppointmentDto;
use App\Manager\AppointmentManager;
use App\Dto\Appointment\CancelAppointmentDto;

#[Route('/appointments')]
class AppointmentController extends AbstractController
{
    #[Route('', name: 'app_appointment_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(
        AppointmentRepository $appointmentRepository,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();
        
        $upcomingAppointments = $appointmentRepository->findUpcomingAppointmentsByStatus($user);
        $pastAppointments = $appointmentRepository->findPastAppointmentsByStatus($user);
        $cancelledAppointments = $appointmentRepository->findCancelledAppointments($user);

        // Get all therapists
        $therapists = $entityManager->getRepository(User::class)->findAllTherapists();

        return $this->render('app/appointment/index.html.twig', [
            'upcoming_appointments' => $upcomingAppointments,
            'past_appointments' => $pastAppointments,
            'cancelled_appointments' => $cancelledAppointments,
            'therapists' => $therapists,
        ]);
    }

    #[Route('/new', name: 'app_appointment_new', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, AppointmentManager $appointmentManager, EmailService $emailService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $dto = new CreateAppointmentDto();
        $dto->therapistId = $request->request->get('therapistId');
        $dto->date = $request->request->get('date');
        $dto->hour = $request->request->get('hour');
        $appointment = $appointmentManager->createFromDto($dto, $user);
        $emailService->sendAppointmentConfirmation($appointment);
        $this->addFlash('success', 'Appointment booked successfully!');
        return $this->redirectToRoute('app_appointment_index');
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

        return $this->render('app/appointment/show.html.twig', [
            'appointment' => $appointment,
        ]);
    }

    #[Route('/cancel', name: 'app_appointment_cancel', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(Request $request, AppointmentManager $appointmentManager, EmailService $emailService): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $dto = new CancelAppointmentDto();
        $dto->appointmentId = $request->request->get('appointmentId');
        $appointment = $appointmentManager->cancelByDto($dto, $user);
        $emailService->sendAppointmentCancellation($appointment);
        $this->addFlash('success', 'Appointment cancelled successfully.');
        return $this->redirectToRoute('app_appointment_index');
    }
} 
