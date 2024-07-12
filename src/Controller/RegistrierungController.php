<?php

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

class RegistrierungController extends AbstractController
{
    #[Route('/registrierung', name: 'app_registrierung')]
    public function registrierung(
        Request $request,
        ManagerRegistry $managerRegistry,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        $regForm = $this->createFormBuilder()
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

        $regForm->handleRequest($request);

        if ($regForm->isSubmitted() && $regForm->isValid()) {
            $formData = $regForm->getData();

            //dump($formData);

            $user = new User();
            $user->setUsername($formData['username']);
            $user->setPassword($userPasswordHasher->hashPassword($user, $formData['password']));

            $entityManager = $managerRegistry->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('registrierung/registrierung.html.twig', [
            'controller_name' => 'RegistrierungController',
            'registrierungForm' => $regForm->createView(),
        ]);
    }
}
