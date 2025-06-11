<?php

namespace App\Service;

use App\Entity\Appointment;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private string $appName = 'TheraTrack',
        private ?Environment $twig = null
    ) {
    }

    public function sendAppointmentConfirmation(Appointment $appointment): void
    {
        $this->sendEmail(
            $appointment->getClient()->getEmail(),
            'Appointment Confirmation',
            $this->getAppointmentConfirmationTemplate($appointment)
        );

        $this->sendEmail(
            $appointment->getTherapist()->getEmail(),
            'New Appointment Scheduled',
            $this->getTherapistNotificationTemplate($appointment)
        );
    }

    public function sendAppointmentCancellation(Appointment $appointment): void
    {
        $this->sendEmail(
            $appointment->getClient()->getEmail(),
            'Appointment Cancelled',
            $this->getCancellationTemplate($appointment, 'client')
        );

        $this->sendEmail(
            $appointment->getTherapist()->getEmail(),
            'Appointment Cancelled',
            $this->getCancellationTemplate($appointment, 'therapist')
        );
    }

    public function sendAppointmentReminder(Appointment $appointment): void
    {
        $html = $this->twig->render('emails/appointment_reminder.html.twig', [
            'appointment' => $appointment
        ]);
        $this->sendEmail(
            $appointment->getClient()->getEmail(),
            'Appointment Reminder',
            $html
        );
    }

    public function sendEmail(string $to, string $subject, string $html): void
    {
        $email = (new Email())
            ->from('noreply@' . $this->appName . '.com')
            ->to($to)
            ->subject($subject)
            ->html($html);

        $this->mailer->send($email);
    }

    private function getAppointmentConfirmationTemplate(Appointment $appointment): string
    {
        return "
            <h1>Appointment Confirmation</h1>
            <p>Dear {$appointment->getClient()->getFullName()},</p>
            <p>Your appointment has been confirmed with {$appointment->getTherapist()->getFullName()}.</p>
            <p><strong>Date:</strong> {$appointment->getStartTime()->format('F j, Y')}</p>
            <p><strong>Time:</strong> {$appointment->getStartTime()->format('g:i A')} - {$appointment->getEndTime()->format('g:i A')}</p>
            <p><strong>Price:</strong> \${$appointment->getPrice()}</p>
            <p>If you need to cancel or reschedule, please do so at least 24 hours in advance.</p>
        ";
    }

    private function getTherapistNotificationTemplate(Appointment $appointment): string
    {
        return "
            <h1>New Appointment Scheduled</h1>
            <p>Dear {$appointment->getTherapist()->getFullName()},</p>
            <p>A new appointment has been scheduled with {$appointment->getClient()->getFullName()}.</p>
            <p><strong>Date:</strong> {$appointment->getStartTime()->format('F j, Y')}</p>
            <p><strong>Time:</strong> {$appointment->getStartTime()->format('g:i A')} - {$appointment->getEndTime()->format('g:i A')}</p>
            <p><strong>Price:</strong> \${$appointment->getPrice()}</p>
        ";
    }

    private function getCancellationTemplate(Appointment $appointment, string $recipient): string
    {
        $otherParty = $recipient === 'client' ? $appointment->getTherapist() : $appointment->getClient();
        
        return "
            <h1>Appointment Cancelled</h1>
            <p>Dear " . ($recipient === 'client' ? $appointment->getClient()->getFullName() : $appointment->getTherapist()->getFullName()) . ",</p>
            <p>Your appointment with {$otherParty->getFullName()} has been cancelled.</p>
            <p><strong>Date:</strong> {$appointment->getStartTime()->format('F j, Y')}</p>
            <p><strong>Time:</strong> {$appointment->getStartTime()->format('g:i A')} - {$appointment->getEndTime()->format('g:i A')}</p>
        ";
    }

    private function getReminderTemplate(Appointment $appointment): string
    {
        return "
            <h1>Appointment Reminder</h1>
            <p>Dear {$appointment->getClient()->getFullName()},</p>
            <p>This is a reminder for your appointment with {$appointment->getTherapist()->getFullName()}.</p>
            <p><strong>Date:</strong> {$appointment->getStartTime()->format('F j, Y')}</p>
            <p><strong>Time:</strong> {$appointment->getStartTime()->format('g:i A')} - {$appointment->getEndTime()->format('g:i A')}</p>
            <p>Please arrive on time. If you need to cancel, do so at least 24 hours in advance.</p>
        ";
    }
} 