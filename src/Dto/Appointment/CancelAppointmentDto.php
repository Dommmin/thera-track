<?php

namespace App\Dto\Appointment;

use Symfony\Component\Validator\Constraints as Assert;

class CancelAppointmentDto
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public string $appointmentId;
} 