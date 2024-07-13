<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\GerichtRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(GerichtRepository $gerichtRepository): Response
    {
        $gerichte = $gerichtRepository->findAll();

        $randomGerichte = array_rand($gerichte, 2);

        /*dump(
            $gerichte[$randomGerichte[0]],
            $gerichte[$randomGerichte[1]]
        );*/

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'gericht1' => $gerichte[$randomGerichte[0]],
            'gericht2' => $gerichte[$randomGerichte[1]],
        ]);
    }
}
