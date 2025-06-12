<?php

namespace App\Tests\Fixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TherapistFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setFirstName('Jan');
        $user->setLastName('Kowalski');
        $user->setEmail('jan.kowalski@example.com');
        $user->setPassword('test'); // UWAGA: w realnym projekcie haszuj hasło
        $user->setRoles(['ROLE_THERAPIST']);
        $user->setSlug('jan-kowalski');
        $user->setIsVerified(true);
        $manager->persist($user);
        $manager->flush();
    }
} 