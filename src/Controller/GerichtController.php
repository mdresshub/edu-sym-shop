<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Gericht;
use App\Form\GerichtType;
use App\Repository\GerichtRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/gericht', name: 'app_gericht.')]
class GerichtController extends AbstractController
{
    #[Route('/', name: 'bearbeiten')]
    public function index(GerichtRepository $gerichtRepository): Response
    {
        $gerichte = $gerichtRepository->findAll();

        return $this->render('gericht/index.html.twig', [
            'controller_name' => 'GerichtController',
            'gerichte' => $gerichte,
        ]);
    }

    #[Route('/anlegen', name: 'anlegen')]
    public function anlegen(Request $request, ManagerRegistry $managerRegistry): Response
    {
        $gericht = new Gericht();

        $form = $this->createForm(GerichtType::class, $gericht);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $bild = $request->files->get('gericht')['anhang'];
            //$bild = $form->get('anhang')->getData();

            if ($bild) {
                $bildName = md5(uniqid('', true)) . '.' . $bild->guessExtension();
                $bild->move($this->getParameter('bilder_ordner'), $bildName);
                $gericht->setBild($bildName);
            }

            $em = $managerRegistry->getManager();
            $em->persist($gericht);
            $em->flush();

            //return $this->redirect($this->generateUrl('app_gericht.bearbeiten'));
            return $this->redirectToRoute('app_gericht.bearbeiten');
        }

        return $this->render('gericht/anlegen.html.twig', [
            'controller_name' => 'GerichtController',
            'anlegen_form' => $form->createView(),
        ]);
    }

    #[Route('/entfernen/{id}', name: 'entfernen')]
    public function entfernen(int $id, GerichtRepository $gerichtRepository, ManagerRegistry $managerRegistry): Response
    {
        $gericht = $gerichtRepository->find($id);

        if ($gericht) {
            $em = $managerRegistry->getManager();
            $em->remove($gericht);
            $em->flush();
        }

        $this->addFlash('erfolg', 'Gericht wurde erfolgreich entfernt!');

        return $this->redirectToRoute('app_gericht.bearbeiten');
    }

    #[Route('/anzeigen/{id}', name: 'anzeigen')]
    public function anzeigen(Gericht $gericht): Response
    {
        //dump($gericht);

        //$gericht = $gerichtRepository->find($id);

        return $this->render('gericht/anzeigen.html.twig', [
            'controller_name' => 'GerichtController',
            'gericht' => $gericht,
        ]);
    }
}
