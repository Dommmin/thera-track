<?php

namespace App\Entity;

use App\Repository\AvailabilityRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AvailabilityRepository::class)]
class Availability
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'availabilities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $therapist = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: 23)]
    private ?int $startHour = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Range(min: 0, max: 23)]
    #[Assert\GreaterThan(propertyPath: 'startHour')]
    private ?int $endHour = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 7)]
    private ?int $dayOfWeek = null;

    #[ORM\Column]
    private ?bool $isAvailable = true;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $excludedDate = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getStartHour(): ?int
    {
        return $this->startHour;
    }

    public function setStartHour(int $startHour): static
    {
        $this->startHour = $startHour;
        return $this;
    }

    public function getEndHour(): ?int
    {
        return $this->endHour;
    }

    public function setEndHour(int $endHour): static
    {
        $this->endHour = $endHour;
        return $this;
    }

    public function getDayOfWeek(): ?int
    {
        return $this->dayOfWeek;
    }

    public function setDayOfWeek(int $dayOfWeek): static
    {
        $this->dayOfWeek = $dayOfWeek;
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
        return $this->dayOfWeek === 6 || $this->dayOfWeek === 7;
    }

    public function isExcludedDate(\DateTimeInterface $date): bool
    {
        return $this->excludedDate !== null && $this->excludedDate->format('Y-m-d') === $date->format('Y-m-d');
    }
} 