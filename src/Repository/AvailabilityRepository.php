<?php

namespace App\Repository;

use App\Entity\Availability;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Availability>
 */
class AvailabilityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Availability::class);
    }

    public function findAvailableSlots(User $therapist, \DateTimeInterface $date): array
    {
        $dayOfWeek = (int)$date->format('N'); // 1 (Monday) to 7 (Sunday)

        // If it's a weekend, return empty array
        if ($dayOfWeek === 6 || $dayOfWeek === 7) {
            return [];
        }

        $qb = $this->createQueryBuilder('a')
            ->andWhere('a.therapist = :therapist')
            ->andWhere('a.dayOfWeek = :dayOfWeek')
            ->andWhere('a.isAvailable = :isAvailable')
            ->andWhere('a.excludedDate IS NULL OR a.excludedDate != :date')
            ->setParameter('therapist', $therapist)
            ->setParameter('dayOfWeek', $dayOfWeek)
            ->setParameter('isAvailable', true)
            ->setParameter('date', $date->format('Y-m-d'));

        return $qb->getQuery()->getResult();
    }

    public function createDefaultAvailability(User $therapist): void
    {
        $em = $this->getEntityManager();

        // Create default availability for Monday to Friday
        for ($day = 1; $day <= 5; $day++) {
            $availability = new Availability();
            $availability->setTherapist($therapist);
            $availability->setDayOfWeek($day);
            $availability->setStartHour(9); // 9 AM
            $availability->setEndHour(17); // 5 PM
            $availability->setIsAvailable(true);

            $em->persist($availability);
        }

        $em->flush();
    }

    public function findExcludedDates(User $therapist): array
    {
        $qb = $this->createQueryBuilder('a')
            ->select('a.excludedDate')
            ->andWhere('a.therapist = :therapist')
            ->andWhere('a.excludedDate IS NOT NULL')
            ->setParameter('therapist', $therapist);

        return $qb->getQuery()->getResult();
    }

    public function findTherapistAvailability(User $therapist, \DateTime $startDate, \DateTime $endDate): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.therapist = :therapist')
            ->andWhere('a.date BETWEEN :startDate AND :endDate')
            ->setParameter('therapist', $therapist)
            ->setParameter('startDate', $startDate)
            ->setParameter('endDate', $endDate)
            ->orderBy('a.date', 'ASC')
            ->addOrderBy('a.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 