<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SettingsForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Repository\UserRepository;

#[Route('/settings')]
class SettingsController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SluggerInterface $slugger
    ) {
    }

    #[Route('', name: 'app_settings', methods: ['GET', 'POST'])]
    public function index(Request $request, UserRepository $userRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $form = $this->createForm(SettingsForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle avatar upload
            $avatarFile = $form->get('avatarFile')->getData();
            if ($avatarFile) {
                $originalFilename = pathinfo($avatarFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $this->slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid('', true).'.'.$avatarFile->guessExtension();

                try {
                    $avatarFile->move(
                        $this->getParameter('avatars_directory'),
                        $newFilename
                    );
                    $user->setAvatar('/uploads/avatars/'.$newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'There was an error uploading your avatar.');
                }
            }

            $userRepository->updateUserSettings($user);
            $this->addFlash('success', 'Your settings have been updated successfully.');

            return $this->redirectToRoute('app_settings');
        }

        return $this->render('app/settings/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/avatar/delete', name: 'app_settings_avatar_delete', methods: ['POST'])]
    public function deleteAvatar(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if ($user->getAvatar()) {
            $avatarPath = $this->getParameter('kernel.project_dir') . '/public' . $user->getAvatar();
            if (file_exists($avatarPath)) {
                unlink($avatarPath);
            }
            $user->setAvatar(null);
            $this->entityManager->flush();
            $this->addFlash('success', 'Avatar has been deleted successfully.');
        }

        return $this->redirectToRoute('app_settings');
    }
} 
