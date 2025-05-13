<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\Bridge\PHPUnit;

use LogicException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\Attributes\After;
use ShipMonk\MemoryScanner\ObjectDeallocationChecker;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;
use function count;
use function in_array;

/**
 * @phpstan-require-extends KernelTestCase
 * @mixin KernelTestCase
 */
trait ObjectDeallocationCheckerKernelTestCaseTrait
{

    private ?ObjectDeallocationChecker $deallocationChecker = null;

    protected function getDeallocationChecker(): ObjectDeallocationChecker
    {
        $this->deallocationChecker ??= new ObjectDeallocationChecker();
        return $this->deallocationChecker;
    }

    /**
     * @return list<string>
     */
    protected function getIgnoredServiceLeaks(): array
    {
        return [];
    }

    #[After(priority: 999_999)]
    protected function beforeKernelTearDown(): void
    {
        if (static::$kernel === null || !$this->status()->isSuccess()) { // @phpstan-ignore method.internal, method.internalClass
            return;
        }

        $container = static::$kernel->getContainer();
        $ignoredServiceLeaks = $this->getIgnoredServiceLeaks();

        if (!$container instanceof Container) {
            throw new LogicException('Container is not an instance of Symfony\Component\DependencyInjection\Container');
        }

        $this->getDeallocationChecker()->expectDeallocation($container, 'container');

        foreach ($container->getServiceIds() as $serviceId) {
            if ($container->initialized($serviceId) && !in_array($serviceId, $ignoredServiceLeaks, strict: true)) {
                $service = $container->get($serviceId);
                $this->getDeallocationChecker()->expectDeallocation($service, "service {$serviceId}");
            }
        }
    }

    #[After(priority: -999_999)]
    protected function afterKernelTearDown(): void
    {
        if ($this->deallocationChecker === null) {
            return;
        }

        $deallocationChecker = $this->deallocationChecker;
        $this->deallocationChecker = null;

        $leakCauses = $deallocationChecker->checkDeallocations();

        if (count($leakCauses) > 0) {
            Assert::fail($deallocationChecker->explainLeaks($leakCauses));
        }
    }

}
