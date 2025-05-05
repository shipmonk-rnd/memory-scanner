<?php declare(strict_types = 1);

namespace ShipMonkTests\MemoryScanner;

use DateTimeImmutable;
use ShipMonk\MemoryScanner\ObjectDeallocationChecker;

class ObjectDeallocationCheckerTest extends MemoryScannerTestCase
{

    public static mixed $leakTest = null;

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
        self::assertSame('', $objectDeallocationChecker->explainLeaks($leaks));
    }

}
