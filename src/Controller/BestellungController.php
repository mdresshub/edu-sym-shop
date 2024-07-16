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
        $bestellung->setBestellNummer($this->generateOrderNumber());
        $bestellung->setName($gericht->getName());
        $bestellung->setPreis($gericht->getPreis());
        $bestellung->setStatus('offen');

        $entityManager = $managerRegistry->getManager();
        $entityManager->persist($bestellung);
        $entityManager->flush();

        $this->addFlash('bestellung', $bestellung->getName() . ' wurde erfolgreich zur Bestellung hinzugefügt.');

        return $this->redirectToRoute('app_menue');
    }

    #[Route('/bestellung/{id}/status/{status}', name: 'app_bestellung_status')]
    public function bestellungStatus(Bestellung $bestellung, string $status, ManagerRegistry $managerRegistry): Response
    {
        $bestellung->setStatus($status);

        $entityManager = $managerRegistry->getManager();
        $entityManager->persist($bestellung);
        $entityManager->flush();

        $this->addFlash('bestellung', $bestellung->getName() . ' wurde erfolgreich auf ' . $status . ' gesetzt.');

        return $this->redirectToRoute('app_bestellung');
    }

    #[Route('/bestellung/{id}/entfernen', name: 'app_bestellung_entfernen')]
    public function bestellungEntfernen(Bestellung $bestellung, ManagerRegistry $managerRegistry): Response
    {
        $entityManager = $managerRegistry->getManager();
        $entityManager->remove($bestellung);
        $entityManager->flush();

        $this->addFlash('bestellung', $bestellung->getName() . ' wurde erfolgreich entfernt.');

        return $this->redirectToRoute('app_bestellung');
    }

    private function generateOrderNumber(): string
    {
        $timeComponent = time();
        $randomComponent = random_int(100, 999);

        return (string) ($timeComponent . $randomComponent);
    }
}
