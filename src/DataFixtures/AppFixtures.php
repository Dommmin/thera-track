<?php

namespace App\DataFixtures;

use App\Entity\Availability;
use App\Entity\Specialization;
use App\Entity\User;
use Carbon\Carbon;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('pl_PL');

        $specializations = [];

        for ($i = 0; $i < 10; $i++) {
            $specialization = new Specialization();
            $specialization->setName($faker->jobTitle);

            $manager->persist($specialization);
            $specializations[] = $specialization;
        }

        $therapists = [];

        for ($i = 0; $i < 10; $i++) {
            $specialization = $faker->randomElement($specializations);

            $firstName = $faker->firstName;
            $gender = str_ends_with($firstName, 'a') ? 'female' : 'male';

            $therapist = new User();
            $therapist->setEmail($faker->email);
            $therapist->setFirstName($faker->firstName);
            $therapist->setLastName($faker->lastName);
            $therapist->setPassword('password');
            $therapist->setRoles(['ROLE_THERAPIST']);
            $therapist->setHourlyRate($faker->randomFloat(2, 50, 500));
            $therapist->setPhone($faker->phoneNumber);
            $therapist->setLocation($faker->city);
            $therapist->setLatitude($faker->latitude(49.0, 54.8));
            $therapist->setLongitude($faker->longitude(14.1, 24.2));
            $therapist->setSpecialization($specialization);
            $therapist->setIsVerified(true);
            $therapist->setBio($faker->realText);

            $therapist->setPassword(
                $this->passwordHasher->hashPassword($therapist, 'password')
            );

            $manager->persist($therapist);
            $therapists[] = $therapist;
        }

        foreach ($therapists as $therapist) {
            for ($i = 1; $i <= 5; $i++) {
                $availability = new Availability();
                $availability->setTherapist($therapist);
                $availability->setDayOfWeek($i);
                $availability->setStartHour(Carbon::parse('09:00'));
                $availability->setEndHour(Carbon::parse('17:00'));

                $manager->persist($availability);
            }
        }

        $manager->flush();
    }
}
