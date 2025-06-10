<?php

namespace App\Repository;

use App\Entity\Appointment;
use App\Entity\User;
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
        return $this->createQueryBuilder('a')
            ->andWhere('a.therapist = :therapist')
            ->andWhere('DATE(a.startTime) = :date')
            ->setParameter('therapist', $therapist)
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getResult();
    }
} 