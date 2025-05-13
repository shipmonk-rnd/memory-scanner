<?php declare(strict_types = 1);

namespace ShipMonkTests\MemoryScanner\Bridge\PHPUnit;

use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Attribute\Route;
use function random_int;

class TestKernel extends Kernel
{

    use MicroKernelTrait;

    public function getCacheDir(): string
    {
        return __DIR__ . '/../../../cache/symfony';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'test' => true,
        ]);

        $container->services()->set('logger', NullLogger::class);
    }

    #[Route('/random/{limit}', name: 'random_number')]
    public function randomNumber(int $limit): JsonResponse
    {
        return new JsonResponse([
            'number' => random_int(0, $limit),
        ]);
    }

}
