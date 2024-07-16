<?php

declare(strict_types=1);

namespace App\Tests\Unit\Controller;

use App\Controller\BestellungController;
use App\Entity\Bestellung;
use App\Entity\Gericht;
use App\Repository\BestellungRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\DependencyInjection\ContainerInterface as DiContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Twig\Environment as TwigEnvironment;

class BestellungControllerTest extends TestCase
{
    public function testBestellung(): void
    {
        $bestellungRepository = $this->createMock(BestellungRepository::class);
        $bestellung = $this->createMock(Bestellung::class);

        $bestellungRepository
            ->expects($this->once())
            ->method('findBy')
            ->with(['tisch' => 'tisch1'])
            ->willReturn([$bestellung]);

        $controller = new BestellungController();
        $controller->setContainer($this->createContainerWithTwigMock());
        $response = $controller->bestellung($bestellungRepository);

        $this->assertEquals( 200 , $response->getStatusCode());
        //$this->assertContains( 'TBD!!!' , $response->getContent());
    }

    public function testBestellen(): void
    {
        $gericht = $this->createGerichtMock();
        $managerRegistry = $this->createManagerRegistryMock();

        $controller = new BestellungController();
        $controller->setContainer($this->getContainerWithSession([
                'type' => 'bestellung',
                'message' => 'Pizza wurde erfolgreich zur Bestellung hinzugefügt.',
            ],
            '/menue'
        ));

        $response = $controller->bestellen($gericht, $managerRegistry);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals( 302 , $response->getStatusCode());
        $this->assertEquals('/menue', $response->getTargetUrl());
    }

    public function testBestellungStatus(): void
    {
        $bestellung = $this->createMock(Bestellung::class);
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $entityManager = $this->getMockBuilder(EntityManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $bestellung
            ->expects($this->once())
            ->method('setStatus')
            ->with('offen');

        $bestellung
            ->expects($this->once())
            ->method('getName')
            ->willReturn('Pizza');

        $managerRegistry
            ->expects($this->once())
            ->method('getManager')
            ->willReturn($entityManager);

        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($bestellung);

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $controller = new BestellungController();
        $controller->setContainer($this->getContainerWithSession([
                'type' => 'bestellung',
                'message' => 'Pizza wurde erfolgreich auf offen gesetzt.',
            ],
            '/menue'
        ));
        $response = $controller->bestellungStatus($bestellung, 'offen', $managerRegistry);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals( 302 , $response->getStatusCode());
        $this->assertEquals('/menue', $response->getTargetUrl());
    }

    public function testBestellungEntfernen(): void
    {
        $bestellung = $this->createMock(Bestellung::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $managerRegistry = $this->createMock(ManagerRegistry::class);

        $bestellung
            ->method('getName')
            ->willReturn('Pizza');

        $managerRegistry
            ->method('getManager')
            ->willReturn($entityManager);

        $entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($bestellung);

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $controller = new BestellungController();
        $controller->setContainer($this->getContainerWithSession([
                'type' => 'bestellung',
                'message' => 'Pizza wurde erfolgreich entfernt.',
            ],
            '/bestellung'
        ));
        $response = $controller->bestellungEntfernen($bestellung, $managerRegistry);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertEquals('/bestellung', $response->getTargetUrl());
    }

    // todo: implement additional test methods to achieve 100% test coverage

    private function createGerichtMock(): MockObject|Gericht
    {
        $gericht = $this->createMock(Gericht::class);
        $gericht->method('getId')->willReturn(1);
        $gericht->method('getName')->willReturn('Pizza');
        $gericht->method('getPreis')->willReturn(10.0);
        return $gericht;
    }

    private function createManagerRegistryMock(): MockObject|ManagerRegistry
    {
        $managerRegistry = $this->createMock(ManagerRegistry::class);
        $entityManager = $this->getMockBuilder(\Doctrine\ORM\EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with(
                $this->callback(function ($bestellung) {
                    return $bestellung instanceof Bestellung &&
                        $bestellung->getTisch() === 'tisch1' &&
                        $bestellung->getBestellNummer() === 1 &&
                        $bestellung->getName() === 'Pizza' &&
                        $bestellung->getPreis() === 10.0 &&
                        $bestellung->getStatus() === 'offen';
                })
            );
        $entityManager
            ->expects($this->once())
            ->method('flush');

        $managerRegistry
            ->method('getManager')
            ->willReturn($entityManager);

        return $managerRegistry;
    }

    private function createSessionWithFlashBag(array $flashMessage): Session
    {
        $flashBag = $this->createMock(FlashBagInterface::class);
        $flashBag
            ->expects($this->once())
            ->method('add')
            ->with(
                $flashMessage['type'],
                $flashMessage['message'],
            );
        $flashBag
            ->method('getName')
            ->willReturn('flashes');
        $flashBag
            ->method('getStorageKey')
            ->willReturn('flashes');

        return new Session(
            storage: new MockArraySessionStorage(),
            flashes: $flashBag,
        );
    }

    private function createContainerWithTwigMock(): MockObject|ContainerInterface
    {
        $containerMock = $this->createMock(ContainerInterface::class);
        $twigServiceMock = $this->createMock(TwigEnvironment::class);

        $containerMock
            ->expects($this->once())
            ->method('has')
            ->willReturn(true);
        $containerMock
            ->expects($this->once())
            ->method('get')
            ->with('twig')
            ->willReturn($twigServiceMock);

        return $containerMock;
    }

    private function getContainerWithSession(array $flashMessage, string $routerUrl): MockObject|DiContainerInterface
    {
        $session = $this->createSessionWithFlashBag($flashMessage);
        $container = $this->createMock(DiContainerInterface::class);
        $requestStack = $this->createMock(RequestStack::class);
        $router = $this->createMock(Router::class);

        $requestStack
            ->method('getSession')
            ->willReturn($session);

        $router
            ->method('generate')
            ->willReturn($routerUrl);

        $container
            ->method('get')
            ->willReturnMap([
                ['twig', $this->createMock(TwigEnvironment::class)],
                ['request_stack', $requestStack],
                ['router', $router],
            ]);

        return $container;
    }
}
