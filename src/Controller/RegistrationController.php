<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\PatientRegistrationForm;
use App\Form\TherapistRegistrationForm;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use App\Service\UserRegistrationService;

class RegistrationController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier, private Security $security)
    {
    }

    #[Route('/register', name: 'app_register')]
    public function registerPatient(Request $request, UserRegistrationService $registrationService): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(PatientRegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registrationService->register(
                $user,
                $form->get('plainPassword')->getData(),
                ['ROLE_PATIENT']
            );

            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('test@example.com', 'Test'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('app/registration/confirmation_email.html.twig')
            );

            $this->security->login($user);

            $this->addFlash('success', 'Registration successful! Please check your email to verify your account.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('app/patient/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/register/therapist', name: 'app_register_therapist')]
    public function registerTherapist(Request $request, UserRegistrationService $registrationService): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $form = $this->createForm(TherapistRegistrationForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $registrationService->register(
                $user,
                $form->get('plainPassword')->getData(),
                ['ROLE_THERAPIST']
            );

            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('test@example.com', 'Test'))
                    ->to((string) $user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('app/registration/confirmation_email.html.twig')
            );

            $this->security->login($user);

            $this->addFlash('success', 'Registration successful! Please check your email to verify your account.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('app/therapist/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            /** @var User $user */
            $user = $this->getUser();
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_home');
    }
}
