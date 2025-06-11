<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserRegistrationService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function register(User $user, string $plainPassword, array $roles): User
    {
        $user->setRoles($roles);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $plainPassword)
        );
        $this->userRepository->createUser($user);
        return $user;
    }
} 