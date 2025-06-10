<?php

namespace App\Service;

use App\Entity\Appointment;
use App\Entity\Availability;
use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GoogleCalendarService
{
    private Google_Client $client;
    private Google_Service_Calendar $service;

    public function __construct(
        private readonly ParameterBagInterface $params
    ) {
        $this->client = new Google_Client();
        $this->client->setApplicationName('TheraTrack');
        $this->client->setScopes(Google_Service_Calendar::CALENDAR);
        
        // Configure client using environment variables
        $this->client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
        $this->client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
        $this->client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        
        $this->service = new Google_Service_Calendar($this->client);
    }

    public function addAppointment(Appointment $appointment): void
    {
        if (!$this->service) {
            return;
        }

        $event = new Google_Service_Calendar_Event([
            'summary' => 'Therapy Session',
            'description' => "Session with {$appointment->getClient()->getFullName()}",
            'start' => [
                'dateTime' => $appointment->getStartTime()->format(\DateTime::RFC3339),
                'timeZone' => 'UTC',
            ],
            'end' => [
                'dateTime' => $appointment->getEndTime()->format(\DateTime::RFC3339),
                'timeZone' => 'UTC',
            ],
            'attendees' => [
                ['email' => $appointment->getTherapist()->getEmail()],
                ['email' => $appointment->getClient()->getEmail()],
            ],
        ]);

        $this->service->events->insert('primary', $event);
    }

    public function addAvailability(Availability $availability): void
    {
        if (!$this->service) {
            return;
        }

        // Create a DateTime object for the current date
        $date = new \DateTime();
        // Set the day of week (1 = Monday, 7 = Sunday)
        $date->setISODate($date->format('Y'), $date->format('W'), $availability->getDayOfWeek());
        
        // Create start and end times
        $startTime = clone $date;
        $startTime->setTime($availability->getStartHour(), 0, 0);
        
        $endTime = clone $date;
        $endTime->setTime($availability->getEndHour(), 0, 0);

        $event = new Google_Service_Calendar_Event([
            'summary' => 'Available for Appointments',
            'description' => 'This time slot is available for booking',
            'start' => [
                'dateTime' => $startTime->format(\DateTime::RFC3339),
                'timeZone' => 'UTC',
            ],
            'end' => [
                'dateTime' => $endTime->format(\DateTime::RFC3339),
                'timeZone' => 'UTC',
            ],
            'transparency' => 'transparent',
        ]);

        $this->service->events->insert('primary', $event);
    }

    public function updateAppointment(Appointment $appointment): void
    {
        if (!$this->service) {
            return;
        }

        // Find the event by searching for the appointment details
        $events = $this->service->events->listEvents('primary', [
            'q' => "Session with {$appointment->getClient()->getFullName()}",
            'timeMin' => $appointment->getStartTime()->format(\DateTime::RFC3339),
        ]);

        foreach ($events->getItems() as $event) {
            if ($event->getStart()->getDateTime() === $appointment->getStartTime()->format(\DateTime::RFC3339)) {
                $event->setStatus($appointment->getStatus() === 'cancelled' ? 'cancelled' : 'confirmed');
                $this->service->events->update('primary', $event->getId(), $event);
                break;
            }
        }
    }

    public function deleteAvailability(Availability $availability): void
    {
        if (!$this->service) {
            return;
        }

        // Find the event by searching for the availability details
        $events = $this->service->events->listEvents('primary', [
            'q' => 'Available for Appointments',
            'timeMin' => $availability->getDate()->format('Y-m-d') . 'T' . $availability->getStartTime()->format('H:i:s'),
        ]);

        foreach ($events->getItems() as $event) {
            if ($event->getStart()->getDateTime() === $availability->getDate()->format('Y-m-d') . 'T' . $availability->getStartTime()->format('H:i:s')) {
                $this->service->events->delete('primary', $event->getId());
                break;
            }
        }
    }
} 
