<?php

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\User;
use App\Entity\AppointmentStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AppointmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Appointment::class);
    }

    public function findUpcomingAppointments(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.startTime > :now')
            ->andWhere('a.therapist = :user OR a.client = :user')
            ->setParameter('now', new \DateTime())
            ->setParameter('user', $user)
            ->orderBy('a.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPastAppointments(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.startTime <= :now')
            ->andWhere('a.therapist = :user OR a.client = :user')
            ->setParameter('now', new \DateTime())
            ->setParameter('user', $user)
            ->orderBy('a.startTime', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAvailableSlots(User $therapist, \DateTime $date): array
    {
        $startOfDay = (clone $date)->setTime(0, 0, 0);
        $endOfDay = (clone $date)->setTime(23, 59, 59);
        return $this->createQueryBuilder('a')
            ->andWhere('a.therapist = :therapist')
            ->andWhere('a.startTime >= :startOfDay')
            ->andWhere('a.startTime <= :endOfDay')
            ->setParameter('therapist', $therapist)
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->getQuery()
            ->getResult();
    }

    public function findAppointmentsForReminder(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.startTime >= :from')
            ->andWhere('a.startTime < :to')
            ->andWhere('a.status = :status')
            ->setParameter('from', $from)
            ->setParameter('to', $to)
            ->setParameter('status', AppointmentStatus::SCHEDULED)
            ->getQuery()
            ->getResult();
    }
} 