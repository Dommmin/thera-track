<?php

namespace App\Entity;

enum AppointmentStatus: string
{
    case SCHEDULED = 'scheduled';
    case UPCOMING = 'upcoming'; // opcjonalnie, jeśli chcesz rozróżniać
    case IN_PROGRESS = 'in_progress';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
} 