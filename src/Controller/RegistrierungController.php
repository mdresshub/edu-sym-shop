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
        UserPasswordHasherInterface $userPasswordHasher,
        ValidatorInterface $validator,
    ): Response {
        $registrierungForm = $this->createFormBuilder()
            ->add('username', TextType::class)
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Die Passwörter müssen übereinstimmen.',
                'required' => true,
                'first_options' => ['label' => 'Passwort'],
                'second_options' => ['label' => 'Passwort wiederholen'],
            ])
            ->add('submit', SubmitType::class)
            ->getForm();

        $registrierungForm->handleRequest($request);

        if ($registrierungForm->isSubmitted() && $registrierungForm->isValid()) {
            $formData = $registrierungForm->getData();

            //dump($formData);

            $user = new User();
            $user->setUsername($formData['username']);
            $user->setRawPassword($formData['password']);
            $user->setPassword($userPasswordHasher->hashPassword($user, $formData['password']));

            $errors = $validator->validate($user);

            if (count($errors) > 0) {
                return $this->render('registrierung/registrierung.html.twig', [
                    'controller_name' => 'RegistrierungController',
                    'registrierungForm' => $registrierungForm->createView(),
                    'errors' => $errors,
                ]);
            }

            $entityManager = $managerRegistry->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('registrierung/registrierung.html.twig', [
            'controller_name' => 'RegistrierungController',
            'registrierungForm' => $registrierungForm->createView(),
            'errors' => [],
        ]);
    }
}
