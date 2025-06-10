<?php

namespace App\Controller\Admin;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/panel', name: 'app_panel')]
    public function index(): Response
    {
        return $this->render('panel/index.html.twig', [
            'controller_name' => 'PanelController',
        ]);
    }
}
