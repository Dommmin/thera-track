<?php

namespace App\Manager;

use App\Dto\Appointment\CreateAppointmentDto;
use App\Dto\Appointment\CancelAppointmentDto;
use App\Entity\Appointment;
use App\Entity\AppointmentStatus;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class AppointmentManager
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository,
        private AppointmentRepository $appointmentRepository,
        private AuthorizationCheckerInterface $authChecker
    ) {}

    public function createFromDto(CreateAppointmentDto $dto, User $client): Appointment
    {
        $therapist = $this->userRepository->find($dto->therapistId);
        if (!$therapist) {
            throw new \InvalidArgumentException('Therapist not found');
        }
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $dto->date . ' ' . $dto->hour);
        if (!$dateTime) {
            throw new \InvalidArgumentException('Invalid date or hour');
        }
        $appointment = new Appointment();
        $appointment->setTherapist($therapist);
        $appointment->setClient($client);
        $appointment->setStartTime($dateTime);
        $appointment->setEndTime((clone $dateTime)->modify('+1 hour'));
        $appointment->setStatus(AppointmentStatus::SCHEDULED);
        $appointment->setPrice($therapist->getHourlyRate() ?? 0);
        $this->em->persist($appointment);
        $this->em->flush();
        return $appointment;
    }

    public function cancelByDto(CancelAppointmentDto $dto, User $user): Appointment
    {
        $appointment = $this->appointmentRepository->find($dto->appointmentId);
        if (!$appointment) {
            throw new \InvalidArgumentException('Appointment not found');
        }
        if (!$this->authChecker->isGranted('cancel', $appointment)) {
            throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException('You cannot cancel this appointment.');
        }
        $appointment->setStatus(AppointmentStatus::CANCELLED);
        $this->em->flush();
        return $appointment;
    }

    public function createFromTherapistDto(\App\Dto\Therapist\BookAppointmentDto $dto, User $therapist, User $client): Appointment
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $dto->date . ' ' . $dto->hour);
        if (!$dateTime) {
            throw new \InvalidArgumentException('Invalid date or hour');
        }
        // Sprawdź, czy slot nie jest już zajęty
        $existing = $this->appointmentRepository->findOneBy([
            'therapist' => $therapist,
            'startTime' => $dateTime
        ]);
        if ($existing) {
            throw new \InvalidArgumentException('This slot is already booked.');
        }
        $appointment = new Appointment();
        $appointment->setTherapist($therapist);
        $appointment->setClient($client);
        $appointment->setStartTime($dateTime);
        $appointment->setEndTime((clone $dateTime)->modify('+1 hour'));
        $appointment->setStatus(AppointmentStatus::SCHEDULED);
        $appointment->setPrice($therapist->getHourlyRate() ?? 0);
        $this->em->persist($appointment);
        $this->em->flush();
        return $appointment;
    }

    public function bookAppointmentForTherapistPage(\App\Dto\Therapist\BookAppointmentDto $dto, User $therapist, User $client): array
    {
        $result = [
            'success' => false,
            'error' => null,
            'appointment' => null,
        ];
        try {
            $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $dto->date . ' ' . $dto->hour);
            if (!$dateTime) {
                $result['error'] = 'Invalid date or hour.';
                return $result;
            }
            $existing = $this->appointmentRepository->findOneBy([
                'therapist' => $therapist,
                'startTime' => $dateTime
            ]);
            if ($existing) {
                $result['error'] = 'This slot is already booked.';
                return $result;
            }
            $appointment = new Appointment();
            $appointment->setTherapist($therapist);
            $appointment->setClient($client);
            $appointment->setStartTime($dateTime);
            $appointment->setEndTime((clone $dateTime)->modify('+1 hour'));
            $appointment->setStatus(AppointmentStatus::SCHEDULED);
            $appointment->setPrice($therapist->getHourlyRate() ?? 0);
            $this->em->persist($appointment);
            $this->em->flush();
            $result['success'] = true;
            $result['appointment'] = $appointment;
        } catch (\Throwable $e) {
            $result['error'] = $e->getMessage();
        }
        return $result;
    }
} 