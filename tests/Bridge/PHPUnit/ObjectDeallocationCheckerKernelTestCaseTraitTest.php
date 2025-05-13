<?php declare(strict_types = 1);

namespace ShipMonkTests\MemoryScanner\Bridge\PHPUnit;

use ShipMonk\MemoryScanner\Bridge\PHPUnit\ObjectDeallocationCheckerKernelTestCaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use function restore_exception_handler;

class ObjectDeallocationCheckerKernelTestCaseTraitTest extends WebTestCase
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
        $client = self::createClient();
        $client->request('GET', '/random/123');

        self::assertResponseIsSuccessful();
    }

    protected function tearDown(): void
    {
        restore_exception_handler();
        parent::tearDown();
    }

}
