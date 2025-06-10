<?php

namespace App\Entity;

use App\Repository\AvailabilityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AvailabilityRepository::class)]
class Availability
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 7)]
    private ?string $dayOfWeek = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: 23)]
    private ?\DateTimeInterface $startHour = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: 23)]
    #[Assert\GreaterThan(propertyPath: 'startHour')]
    private ?\DateTimeInterface $endHour = null;

    #[ORM\ManyToOne(inversedBy: 'availabilities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $therapist = null;

    #[ORM\Column]
    private ?bool $isAvailable = true;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $excludedDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDayOfWeek(): ?string
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(string $dayOfWeek): static
    {
        $this->dayOfWeek = $dayOfWeek;

        return $this;
    }

    public function getStartHour(): ?\DateTimeInterface
    {
        return $this->startHour;
    }

    public function setStartHour(\DateTimeInterface $startHour): static
    {
        $this->startHour = $startHour;

        return $this;
    }

    public function getEndHour(): ?\DateTimeInterface
    {
        return $this->endHour;
    }

    public function setEndHour(\DateTimeInterface $endHour): static
    {
        $this->endHour = $endHour;

        return $this;
    }

    public function getTherapist(): ?User
    {
        return $this->therapist;
    }

    public function setTherapist(?User $therapist): static
    {
        $this->therapist = $therapist;

        return $this;
    }

    public function isAvailable(): ?bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;
        return $this;
    }

    public function getExcludedDate(): ?\DateTimeInterface
    {
        return $this->excludedDate;
    }

    public function setExcludedDate(?\DateTimeInterface $excludedDate): static
    {
        $this->excludedDate = $excludedDate;
        return $this;
    }

    public function isWeekend(): bool
    {
        return $this->dayOfWeek === '6' || $this->dayOfWeek === '7';
    }

    public function isExcludedDate(\DateTimeInterface $date): bool
    {
        return $this->excludedDate !== null && $this->excludedDate->format('Y-m-d') === $date->format('Y-m-d');
    }
} 