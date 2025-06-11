<?php

namespace App\Security\Voter;

use App\Entity\Appointment;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AppointmentVoter extends Voter
{
    public const CANCEL = 'cancel';

    protected function supports(string $attribute, $subject): bool
    {
        return $attribute === self::CANCEL && $subject instanceof Appointment;
    }

    /**
     * @param string $attribute
     * @param Appointment $subject
     * @param TokenInterface $token
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }
        // Pozwól anulować, jeśli użytkownik jest terapeutą lub klientem tej wizyty
        // oraz wizyta nie jest przeszła i nie jest anulowana
        $isOwner = $user === $subject->getTherapist() || $user === $subject->getClient();
        $isFuture = $subject->getStartTime() > new \DateTime();
        $notCancelled = $subject->getStatus() !== 'cancelled';
        $moreThan24h = $subject->getStartTime() > (new \DateTime('+24 hours'));
        return $isOwner && $isFuture && $notCancelled && $moreThan24h;
    }
} 