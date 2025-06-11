<?php

namespace App\Dto\Appointment;

use Symfony\Component\Validator\Constraints as Assert;

class CreateAppointmentDto
{
    #[Assert\NotBlank]
    #[Assert\Date]
    public string $date;

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^\d{2}:\d{2}$/')]
    public string $hour;

    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $therapistId;
} 