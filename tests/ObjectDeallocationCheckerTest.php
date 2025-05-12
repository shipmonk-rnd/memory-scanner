<?php declare(strict_types = 1);

namespace ShipMonkTests\MemoryScanner;

use DateTimeImmutable;
use ShipMonk\MemoryScanner\ObjectDeallocationChecker;
use function register_shutdown_function;

class ObjectDeallocationCheckerTest extends MemoryScannerTestCase
{

    private static mixed $leakTest = null;

    public function testCheckDeallocationsOk(): void
    {
        $a = new DateTimeImmutable();

        $objectDeallocationChecker = new ObjectDeallocationChecker();
        $objectDeallocationChecker->expectDeallocation($a, 'A');
        unset($a);

        self::assertSame([], $objectDeallocationChecker->checkDeallocations());
    }

    public function testExplainLeaks(): void
    {
        $a = new DateTimeImmutable();

        $objectDeallocationChecker = new ObjectDeallocationChecker();
        $objectDeallocationChecker->expectDeallocation($a, 'A');
        self::$leakTest = ['foo' => (object) ['bar' => [$a]]];
        $objectDeallocationChecker->expectDeallocation(self::$leakTest['foo'], 'Foo');
        unset($a);

        $leaks = $objectDeallocationChecker->checkDeallocations();
        self::assertCount(1, $leaks);
        self::assertSnapshot(__DIR__ . '/snapshots/ObjectDeallocationCheckerTest.testExplainLeaks.txt', $objectDeallocationChecker->explainLeaks($leaks));
        self::$leakTest = null;
    }

    public function testExplainLeaksWithUnknownReason(): void
    {
        $a = new DateTimeImmutable();
        register_shutdown_function(static function () use ($a): void {
            unset($a);
        });

        $objectDeallocationChecker = new ObjectDeallocationChecker();
        $objectDeallocationChecker->expectDeallocation($a, 'A');
        unset($a);

        $leaks = $objectDeallocationChecker->checkDeallocations();
        self::assertSnapshot(__DIR__ . '/snapshots/ObjectDeallocationCheckerTest.testExplainLeaksWithUnknownReason.txt', $objectDeallocationChecker->explainLeaks($leaks));
    }

}
