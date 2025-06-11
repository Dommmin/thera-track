<?php

namespace App\Controller\Admin;

use App\Entity\Availability;
use App\Entity\User;
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

        $errors = [];
        if ($request->isMethod('POST')) {
            $dayOfWeek = $request->request->get('dayOfWeek');
            $startHour = $request->request->get('startHour');
            $endHour = $request->request->get('endHour');
            $excludedDate = $request->request->get('excludedDate');

            // Walidacja
            if (!in_array($dayOfWeek, ['1','2','3','4','5'])) {
                $errors[] = 'Invalid day of week.';
            }
            if (!$startHour || !$endHour) {
                $errors[] = 'Start and end time are required.';
            }
            if ($startHour && $endHour && $startHour >= $endHour) {
                $errors[] = 'End time must be after start time.';
            }

            if (empty($errors)) {
                $availability = new Availability();
                $availability->setTherapist($user);
                $availability->setDayOfWeek($dayOfWeek);
                $availability->setStartHour(\DateTime::createFromFormat('H:i', $startHour));
                $availability->setEndHour(\DateTime::createFromFormat('H:i', $endHour));
                $availability->setIsAvailable(true);
                if ($excludedDate) {
                    $availability->setExcludedDate(\DateTime::createFromFormat('Y-m-d', $excludedDate));
                }
                $entityManager->persist($availability);
                $entityManager->flush();
                $this->addFlash('success', 'Availability added successfully!');
                return $this->redirectToRoute('app_therapist_availability');
            }

            foreach ($errors as $err) {
                $this->addFlash('danger', $err);
            }
        }

        $excludedDates = $availabilityRepository->findExcludedDates($user);
        // Możesz też pobrać wszystkie dostępności terapeuty:
        $availabilities = $availabilityRepository->findBy(['therapist' => $user], ['dayOfWeek' => 'ASC', 'startHour' => 'ASC']);

        return $this->render('panel/availability.html.twig', [
            'excluded_dates' => $excludedDates,
            'availabilities' => $availabilities,
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

        return $this->render('app/therapist/settings.html.twig', [
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

        return $this->render('app/therapist/calendar.html.twig', [
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
}
