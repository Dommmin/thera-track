<?php

namespace App\Controller\Admin;

use App\Entity\Availability;
use App\Form\AvailabilityForm;
use App\Form\AvailabilityType;
use App\Repository\AvailabilityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/panel/availability')]
#[IsGranted('ROLE_THERAPIST')]
class AvailabilityController extends AbstractController
{
    #[Route('', name: 'panel_availability_index', methods: ['GET'])]
    public function index(Request $request, AvailabilityRepository $availabilityRepository): Response
    {
        $availabilities = $availabilityRepository->findBy(['therapist' => $this->getUser()]);
        $form = $this->createForm(AvailabilityForm::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $availability = $form->getData();
            $availability->setTherapist($this->getUser());

            $availabilityRepository->save($availability, true);

            $this->addFlash('success', 'Availability has been added successfully.');

            return $this->redirectToRoute('panel_availability_index');
        }

        return $this->render('panel/availability.html.twig', [
            'availabilities' => $availabilities,
            'form' => $form->createView(),
            'excluded_dates' => [],
        ]);
    }

    #[Route('/new', name: 'panel_availability_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $availability = new Availability();
        $availability->setTherapist($this->getUser());
        $form = $this->createForm(AvailabilityType::class, $availability);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($availability);
            $entityManager->flush();
            $this->addFlash('success', 'Availability has been added successfully.');
            return $this->redirectToRoute('panel_availability_index');
        }
        return $this->render('panel/availability_new.html.twig', [
            'availability' => $availability,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'panel_availability_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Availability $availability, EntityManagerInterface $entityManager): Response
    {
        if ($availability->getTherapist() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only edit your own availability.');
        }
        $form = $this->createForm(AvailabilityType::class, $availability);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Availability has been updated successfully.');
            return $this->redirectToRoute('panel_availability_index');
        }
        return $this->render('panel/availability_edit.html.twig', [
            'availability' => $availability,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'panel_availability_delete', methods: ['POST'])]
    public function delete(Request $request, Availability $availability, EntityManagerInterface $entityManager): Response
    {
        if ($availability->getTherapist() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You can only delete your own availability.');
        }
        if ($this->isCsrfTokenValid('delete'.$availability->getId(), $request->request->get('_token'))) {
            $entityManager->remove($availability);
            $entityManager->flush();
            $this->addFlash('success', 'Availability has been deleted successfully.');
        }
        return $this->redirectToRoute('panel_availability_index');
    }
} 
