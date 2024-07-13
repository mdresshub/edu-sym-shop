<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Bestellung;
use App\Entity\Gericht;
use App\Repository\BestellungRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BestellungController extends AbstractController
{
    #[Route('/bestellung', name: 'app_bestellung')]
    public function bestellung(BestellungRepository $bestellungRepository): Response
    {
        $bestellungen = $bestellungRepository->findBy(['tisch' => 'tisch1']);

        return $this->render('bestellung/bestellung.html.twig', [
            'controller_name' => 'BestellungController',
            'bestellungen' => $bestellungen,
        ]);
    }

    #[Route('/bestellen/{id}', name: 'app_bestellen')]
    public function bestellen(Gericht $gericht, ManagerRegistry $managerRegistry): Response
    {
        $bestellung = new Bestellung();
        $bestellung->setTisch('tisch1');
        $bestellung->setBestellNummer($gericht->getId());
        $bestellung->setName($gericht->getName());
        $bestellung->setPreis($gericht->getPreis());
        $bestellung->setStatus('offen');

        $entityManager = $managerRegistry->getManager();
        $entityManager->persist($bestellung);
        $entityManager->flush();

        $this->addFlash('bestellung', $bestellung->getName() . ' wurde erfolgreich zur Bestellung hinzugefügt.');

        return $this->redirectToRoute('app_menue');
    }
}
