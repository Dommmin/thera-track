<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;

#[Route('/panel/therapists', name: 'panel_therapist_')]
class TherapistPanelController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request, UserRepository $userRepository): Response
    {
        $location = $request->query->get('location');
        $search = $request->query->get('search');
        $therapists = $userRepository->findTherapists($location, $search);
        return $this->render('panel/therapists.html.twig', [
            'therapists' => $therapists,
            'location' => $location,
            'search' => $search,
        ]);
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] User $therapist): Response
    {
        if (!in_array('ROLE_THERAPIST', $therapist->getRoles())) {
            throw $this->createNotFoundException('Therapist not found');
        }
        return $this->render('panel/therapist_show.html.twig', [
            'therapist' => $therapist,
        ]);
    }
} 