<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrierungController extends AbstractController
{
    #[Route('/registrierung', name: 'app_registrierung')]
    public function registrierung(
        Request $request,
        ManagerRegistry $managerRegistry,
        UserPasswordHasherInterface $passwordHasher,
        ValidatorInterface $validator
    ): Response {
        $registrierungForm = $this->buildRegistrierungForm();
        $registrierungForm->handleRequest($request);

        if ($registrierungForm->isSubmitted() && $registrierungForm->isValid()) {
            $user = $this->createUserFromForm($registrierungForm, $passwordHasher);
            $errors = $validator->validate($user);

            if (count($errors) > 0) {
                return $this->renderRegistrierungForm($registrierungForm, $errors);
            }

            $this->saveUser($user, $managerRegistry);
            return $this->redirectToRoute('app_home');
        }

        return $this->renderRegistrierungForm($registrierungForm);
    }

    private function buildRegistrierungForm(): FormInterface
    {
        return $this->createFormBuilder()
            ->add('username', TextType::class, ['label' => 'Benutzername'])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Die Passwörter müssen übereinstimmen.',
                'required' => true,
                'first_options' => ['label' => 'Passwort (min. 8 Zeichen, 1 Ziffer, 1 Sonderzeichen, 1 Groß- und 1 Kleinbuchstabe)'],
                'second_options' => ['label' => 'Passwort wiederholen'],
            ])
            ->add('submit', SubmitType::class, ['label' => 'Registrieren'])
            ->getForm();
    }

    private function createUserFromForm($form, UserPasswordHasherInterface $userPasswordHasher): User
    {
        $formData = $form->getData();

        $user = new User();
        $user->setUsername($formData['username']);
        $user->setRawPassword($formData['password']);
        $user->setPassword($userPasswordHasher->hashPassword($user, $formData['password']));

        return $user;
    }

    private function renderRegistrierungForm($form, $errors = []): Response
    {
        return $this->render('registrierung/registrierung.html.twig', [
            'controller_name' => 'RegistrierungController',
            'registrierungForm' => $form->createView(),
            'errors' => $errors,
        ]);
    }

    private function saveUser(User $user, ManagerRegistry $managerRegistry): void
    {
        $entityManager = $managerRegistry->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
    }
}
