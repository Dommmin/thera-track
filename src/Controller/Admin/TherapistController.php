<?php

namespace App\Controller\Admin;

use App\Entity\Availability;
use App\Entity\User;
use App\Form\AvailabilityFormType;
use App\Form\ProfileForm;
use App\Repository\AvailabilityRepository;
use App\Service\GoogleCalendarService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/panel')]
class TherapistController extends AbstractController
{
    public function __construct(
        private GoogleCalendarService $googleCalendarService
    ) {
    }

    #[Route('/availability', name: 'app_therapist_availability', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_THERAPIST')]
    public function availability(Request $request, EntityManagerInterface $entityManager, AvailabilityRepository $availabilityRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $availability = new Availability();
        $availability->setTherapist($user);
        
        $form = $this->createForm(AvailabilityFormType::class, $availability);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($availability);
            $entityManager->flush();

            $this->googleCalendarService->addAvailability($availability);

            $this->addFlash('success', 'Availability added successfully!');
            return $this->redirectToRoute('app_therapist_availability');
        }

        $excludedDates = $availabilityRepository->findExcludedDates($user);

        return $this->render('therapist/availability.html.twig', [
            'form' => $form->createView(),
            'excluded_dates' => $excludedDates,
        ]);
    }

    #[Route('/settings', name: 'app_therapist_settings', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_THERAPIST')]
    public function settings(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ProfileForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Your profile has been updated successfully.');

            return $this->redirectToRoute('app_therapist_settings');
        }

        return $this->render('therapist/settings.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/calendar', name: 'app_therapist_calendar', methods: ['GET'])]
    public function calendar(
        Request $request,
        AvailabilityRepository $availabilityRepository
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $appointments = $availabilityRepository->findAvailableSlots($user, new \DateTime());

        return $this->render('therapist/calendar.html.twig', [
            'appointments' => json_encode($appointments),
        ]);
    }

    #[Route('/availability/{id}/delete', name: 'app_therapist_availability_delete', methods: ['POST'])]
    public function deleteAvailability(
        Request $request,
        Availability $availability,
        EntityManagerInterface $entityManager
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($availability->getTherapist() !== $user) {
            throw $this->createAccessDeniedException('You cannot delete this availability.');
        }

        if ($this->isCsrfTokenValid('delete'.$availability->getId(), $request->request->get('_token'))) {
            $this->googleCalendarService->deleteAvailability($availability);
            
            $entityManager->remove($availability);
            $entityManager->flush();

            $this->addFlash('success', 'Availability deleted successfully.');
        }

        return $this->redirectToRoute('app_therapist_calendar');
    }

    #[Route('/availability/exclude/{date}', name: 'app_therapist_remove_excluded_date', methods: ['POST'])]
    public function removeExcludedDate(
        Request $request,
        string $date,
        EntityManagerInterface $entityManager,
        AvailabilityRepository $availabilityRepository
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        if ($this->isCsrfTokenValid('remove_excluded_date', $request->request->get('_token'))) {
            $excludedDate = new \DateTime($date);
            $availability = $availabilityRepository->findOneBy([
                'therapist' => $user,
                'excludedDate' => $excludedDate,
            ]);

            if ($availability) {
                $entityManager->remove($availability);
                $entityManager->flush();
                $this->addFlash('success', 'Excluded date removed successfully.');
            }
        }

        return $this->redirectToRoute('app_therapist_availability');
    }

//    #[Route('/', name: 'app_therapist_list', methods: ['GET'])]
//    public function list(Request $request, UserRepository $userRepository): Response
//    {
//        $location = $request->query->get('location');
//        $search = $request->query->get('search');
//
//        $therapists = $userRepository->findTherapists($location, $search);
//
//        return $this->render('therapist/list.html.twig', [
//            'therapists' => $therapists,
//            'location' => $location,
//            'search' => $search,
//        ]);
//    }

//    #[Route('/{slug}', name: 'app_therapist_show', methods: ['GET'])]
//    public function show(User $therapist): Response
//    {
//        if (!in_array('ROLE_THERAPIST', $therapist->getRoles())) {
//            throw $this->createNotFoundException('Therapist not found');
//        }
//
//        // Prevent therapists from viewing other therapists' profiles
//        if ($this->isGranted('ROLE_THERAPIST') && $therapist !== $this->getUser()) {
//            throw $this->createAccessDeniedException('You cannot view other therapists\' profiles.');
//        }
//
//        return $this->render('therapist/show.html.twig', [
//            'therapist' => $therapist,
//        ]);
//    }
}
