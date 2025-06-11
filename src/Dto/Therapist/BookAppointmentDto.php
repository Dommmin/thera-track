<?php

namespace App\Dto\Therapist;

use Symfony\Component\Validator\Constraints as Assert;

class BookAppointmentDto
{
    #[Assert\NotBlank]
    #[Assert\Date]
    public string $date;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d{2}:\d{2}$/')]
    public string $hour;
} 