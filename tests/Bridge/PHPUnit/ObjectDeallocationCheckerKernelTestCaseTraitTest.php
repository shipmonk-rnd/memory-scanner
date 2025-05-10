<?php declare(strict_types = 1);

namespace ShipMonkTests\MemoryScanner\Bridge\PHPUnit;

use ShipMonk\MemoryScanner\Bridge\PHPUnit\ObjectDeallocationCheckerKernelTestCaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use function restore_exception_handler;

class ObjectDeallocationCheckerKernelTestCaseTraitTest extends KernelTestCase
{

    use ObjectDeallocationCheckerKernelTestCaseTrait;

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function testNotUsingContainer(): void
    {
        self::assertTrue(true); // @phpstan-ignore staticMethod.alreadyNarrowedType
    }

    public function testUsingContainer(): void
    {
        self::getContainer()->get('logger');
        self::assertTrue(true); // @phpstan-ignore staticMethod.alreadyNarrowedType
    }

    protected function tearDown(): void
    {
        restore_exception_handler();
        parent::tearDown();
    }

}
