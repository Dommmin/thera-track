<?php

namespace App\Command;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:send-appointment-reminders',
    description: 'Send appointment reminders to patients for appointments scheduled for the next day.'
)]
class SendAppointmentRemindersCommand extends Command
{
    public function __construct(
        private AppointmentRepository $appointmentRepository,
        private EmailService $emailService,
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tomorrow = (new \DateTime('tomorrow'))->setTime(0, 0, 0);
        $dayAfter = (clone $tomorrow)->modify('+1 day');
        $appointments = $this->appointmentRepository->findAppointmentsForReminder($tomorrow, $dayAfter);

        $count = 0;
        foreach ($appointments as $appointment) {
            $this->emailService->sendAppointmentReminder($appointment);
            $output->writeln(sprintf(
                'Reminder sent to %s for appointment with %s on %s at %s',
                $appointment->getClient()->getEmail(),
                $appointment->getTherapist()->getFullName(),
                $appointment->getStartTime()->format('Y-m-d'),
                $appointment->getStartTime()->format('H:i')
            ));
            $count++;
        }
        $output->writeln("Sent $count reminders.");
        return Command::SUCCESS;
    }
} 
