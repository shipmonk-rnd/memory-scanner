<?php declare(strict_types = 1);

namespace ShipMonkTests\MemoryScanner;

use DateTimeImmutable;
use ShipMonk\MemoryScanner\MemoryRootsProvider;
use ShipMonk\MemoryScanner\MemoryScanner;
use ShipMonk\MemoryScanner\ObjectReference;
use stdClass;

class MemoryScannerTest extends MemoryScannerTestCase
{

    public function testFindRootsWithNoProviders(): void
    {
        $memoryScanner = new MemoryScanner();
        self::assertSame([], $memoryScanner->findRoots());
    }

    public function testFindRoots(): void
    {
        $memoryRootsProviderA = $this->createMock(MemoryRootsProvider::class);
        $memoryRootsProviderA->expects(self::once())
            ->method('getRoots')
            ->willReturn(['root1' => 1, 'root2' => 2]);

        $memoryRootsProviderB = $this->createMock(MemoryRootsProvider::class);
        $memoryRootsProviderB->expects(self::once())
            ->method('getRoots')
            ->willReturn(['root3' => 3, 'nested' => ['root4' => 4]]);

        $memoryScanner = new MemoryScanner();
        $memoryScanner->registerMemoryRootsProvider($memoryRootsProviderA);
        $memoryScanner->registerMemoryRootsProvider($memoryRootsProviderB);

        self::assertSame(
            ['root1' => 1, 'root2' => 2, 'root3' => 3, 'nested' => ['root4' => 4]],
            $memoryScanner->findRoots(),
        );
    }

    public function testFindObjectReferencesWithNoRoots(): void
    {
        $memoryScanner = new MemoryScanner();
        self::assertCount(0, $memoryScanner->findObjectReferences([]));
    }

    public function testFindObjectReferencesWithObjectCycle(): void
    {
        $a = (object) ['name' => 'A', 'ref' => null];
        $b = (object) ['name' => 'B', 'ref' => $a];
        $c = (object) ['name' => 'C', 'ref' => [$b]];
        $a->ref = $c;

        $memoryScanner = new MemoryScanner();
        $objectReferences = $memoryScanner->findObjectReferences(['root' => $a]);
        self::assertCount(3, $objectReferences);

        self::assertEquals(
            [new ObjectReference(null, ['root']), new ObjectReference($b, ['$ref'])],
            $objectReferences[$a],
        );

        self::assertEquals(
            [new ObjectReference($c, ['$ref', 0])],
            $objectReferences[$b],
        );

        self::assertEquals(
            [new ObjectReference($a, ['$ref'])],
            $objectReferences[$c],
        );
    }

    public function testFindObjectReferencesWithArrayReferenceCycle(): void
    {
        $a = new DateTimeImmutable();
        $b = [0 => &$b, 'ref' => $a]; // @phpstan-ignore variable.undefined
        $c = (object) ['ref' => $b];

        $memoryScanner = new MemoryScanner();
        $objectReferences = $memoryScanner->findObjectReferences(['root' => $c]);
        self::assertCount(2, $objectReferences);

        self::assertEquals(
            [new ObjectReference(null, ['root'])],
            $objectReferences[$c],
        );

        self::assertEquals(
            [new ObjectReference($c, ['$ref', 'ref']), new ObjectReference($c, ['$ref', 0, 'ref'])],
            $objectReferences[$a],
        );
    }

    public function testFindRootReference(): void
    {
        $a = (object) ['name' => 'A', 'ref' => null];
        $b = (object) ['name' => 'B', 'ref' => $a];
        $c = (object) ['name' => 'C', 'ref' => [$b]];
        $a->ref = $c;

        $memoryScanner = new MemoryScanner();
        $objectReferences = $memoryScanner->findObjectReferences(['root' => $a]);

        self::assertEquals(
            [new ObjectReference(null, ['root'])],
            $memoryScanner->findRootReference($a, $objectReferences),
        );

        self::assertEquals(
            [
                new ObjectReference(null, ['root']),
                new ObjectReference($a, ['$ref']),
                new ObjectReference($c, ['$ref', 0]),
            ],
            $memoryScanner->findRootReference($b, $objectReferences),
        );

        self::assertEquals(
            [new ObjectReference(null, ['root']), new ObjectReference($a, ['$ref'])],
            $memoryScanner->findRootReference($c, $objectReferences),
        );

        self::assertNull($memoryScanner->findRootReference(new stdClass(), $objectReferences));
    }

}
