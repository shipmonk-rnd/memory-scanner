<?php declare(strict_types = 1);

namespace ShipMonkTests\MemoryScanner\Bridge\PHPUnit;

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
        $container->parameters()
            ->set('kernel.runtime_mode', ['web' => 0])
            ->set('kernel.trust_x_sendfile_type_header', false)
            ->set('kernel.trusted_hosts', ['localhost'])
            ->set('kernel.trusted_proxies', '0.0.0.0/0')
            ->set('kernel.trusted_headers', ['X-Forwarded-For', 'X-Forwarded-Proto']);

        $container->extension('framework', [
            'test' => true,
        ]);
    }

    #[Route('/random/{limit}', name: 'random_number')]
    public function randomNumber(int $limit): JsonResponse
    {
        return new JsonResponse([
            'number' => random_int(0, $limit),
        ]);
    }

}
