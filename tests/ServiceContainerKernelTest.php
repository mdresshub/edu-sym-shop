<?php

declare(strict_types=1);

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ServiceContainerKernelTest extends KernelTestCase
{
    public function testServiceContainer(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $kernelService = $container->get('kernel');

        $this->assertNotNull($kernelService);
    }
}
