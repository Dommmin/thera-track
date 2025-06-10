<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SessionNoteController extends AbstractController
{
    #[Route('/session/note', name: 'app_session_note')]
    public function index(): Response
    {
        return $this->render('session_note/index.html.twig', [
            'controller_name' => 'SessionNoteController',
        ]);
    }
}
