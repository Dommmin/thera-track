<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TherapistControllerTest extends WebTestCase
{
    public function testListTherapists(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/therapists');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('h1:contains("Find a Therapist")');
    }

    public function testShowTherapist(): void
    {
        $client = static::createClient();
        // Dodaj fixture lub stwórz użytkownika w bazie testowej, jeśli nie istnieje
        // Zakładamy, że istnieje terapeuta o slug 'jan-kowalski'
        $client->request('GET', '/therapists/jan-kowalski');
        $this->assertResponseStatusCodeSame(200);
        $this->assertSelectorExists('a.btn-primary'); // przycisk View Profile lub podobny
    }
} 