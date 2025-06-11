<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\AvailabilityRepository;
use App\Repository\AppointmentRepository;

class AvailabilityService
{
    public function __construct(
        private AvailabilityRepository $availabilityRepository,
        private AppointmentRepository $appointmentRepository
    ) {}

    public function getAvailableHours(User $therapist, \DateTime $date): array
    {
        $dayOfWeek = $date->format('N');
        $availabilities = $this->availabilityRepository->findBy([
            'therapist' => $therapist,
            'dayOfWeek' => $dayOfWeek,
            'isAvailable' => true,
        ]);
        $availabilities = array_filter($availabilities, function($a) use ($date) {
            return !$a->isExcludedDate($date);
        });
        if (empty($availabilities)) {
            return [];
        }
        $appointments = $this->appointmentRepository->findAvailableSlots($therapist, $date);
        $taken = [];
        foreach ($appointments as $app) {
            $taken[] = $app->getStartTime()->format('H:i');
        }
        $slots = [];
        foreach ($availabilities as $a) {
            $start = (clone $date)->setTime((int)$a->getStartHour()->format('H'), 0);
            $end = (clone $date)->setTime((int)$a->getEndHour()->format('H'), (int)$a->getEndHour()->format('i'));
            while ($start < $end) {
                $slotStr = $start->format('H:i');
                if (!in_array($slotStr, $taken)) {
                    $slots[] = $slotStr;
                }
                $start->modify('+1 hour');
            }
        }
        return $slots;
    }
} 