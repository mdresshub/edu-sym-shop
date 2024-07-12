<?php

namespace App\Controller;

use App\Repository\GerichtRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MenueController extends AbstractController
{
    #[Route('/menue', name: 'app_menue')]
    public function menue(GerichtRepository $gerichtRepository): Response
    {
        $gerichte = $gerichtRepository->findAll();

        return $this->render('menue/index.html.twig', [
            'controller_name' => 'MenueController',#
            'gerichte' => $gerichte,
        ]);
    }
}
