<?php

namespace App\Controller\Admin;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/panel/patient')]
#[IsGranted('ROLE_PATIENT')]
class PatientPanelController extends AbstractController
{
    #[Route('/appointments', name: 'panel_patient_appointments', methods: ['GET'])]
    public function appointments(AppointmentRepository $appointmentRepository): Response
    {
        $user = $this->getUser();
        $upcomingAppointments = $appointmentRepository->findUpcomingAppointments($user);
        $pastAppointments = $appointmentRepository->findPastAppointments($user);
        return $this->render('panel/patient_appointments.html.twig', [
            'upcoming_appointments' => $upcomingAppointments,
            'past_appointments' => $pastAppointments,
        ]);
    }

    #[Route('/appointments/{id}/cancel', name: 'panel_patient_appointment_cancel', methods: ['POST'])]
    public function cancel(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        if ($user !== $appointment->getClient()) {
            throw $this->createAccessDeniedException('You cannot cancel this appointment.');
        }
        if ($this->isCsrfTokenValid('cancel'.$appointment->getId(), $request->request->get('_token'))) {
            $appointment->setStatus('cancelled');
            $entityManager->flush();
            $this->addFlash('success', 'Appointment cancelled successfully.');
        }
        return $this->redirectToRoute('panel_patient_appointments');
    }
} 