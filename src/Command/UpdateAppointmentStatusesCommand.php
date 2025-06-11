<?php

namespace App\Command;

use App\Entity\AppointmentStatus;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:update-appointment-statuses',
    description: 'Update appointment statuses based on current time.'
)]
class UpdateAppointmentStatusesCommand extends Command
{
    public function __construct(
        private readonly AppointmentRepository $appointmentRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $now = new \DateTimeImmutable();
        $appointments = $this->appointmentRepository->findAll();
        $updated = 0;
        foreach ($appointments as $appointment) {
            if ($appointment->getStatus() === AppointmentStatus::CANCELLED) {
                continue;
            }
            if ($appointment->getEndTime() < $now && $appointment->getStatus() !== AppointmentStatus::COMPLETED) {
                $appointment->setStatus(AppointmentStatus::COMPLETED);
                $updated++;
            } elseif ($appointment->getStartTime() <= $now && $appointment->getEndTime() > $now && $appointment->getStatus() !== AppointmentStatus::IN_PROGRESS) {
                $appointment->setStatus(AppointmentStatus::IN_PROGRESS);
                $updated++;
            } elseif ($appointment->getStartTime() > $now && $appointment->getStatus() !== AppointmentStatus::SCHEDULED) {
                $appointment->setStatus(AppointmentStatus::SCHEDULED);
                $updated++;
            }
        }
        $this->entityManager->flush();
        $output->writeln("Updated $updated appointments.");
        return Command::SUCCESS;
    }
} 