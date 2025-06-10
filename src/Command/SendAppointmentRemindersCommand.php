<?php

namespace App\Command;

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

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('a')
            ->from('App\\Entity\\Appointment', 'a')
            ->where('a.startTime >= :tomorrow')
            ->andWhere('a.startTime < :dayAfter')
            ->andWhere('a.status = :status')
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('dayAfter', $dayAfter)
            ->setParameter('status', 'scheduled');
        $appointments = $qb->getQuery()->getResult();

        $count = 0;
        foreach ($appointments as $appointment) {
            $this->emailService->sendAppointmentReminder($appointment);
            $count++;
        }
        $output->writeln("Sent $count reminders.");
        return Command::SUCCESS;
    }
} 