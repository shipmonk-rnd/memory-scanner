<?php declare(strict_types = 1);

namespace ShipMonkTests\MemoryScanner\InternalObjectExporter;

use ShipMonk\MemoryScanner\InternalObjectExporter\WeakMapExporter;
use ShipMonkTests\MemoryScanner\MemoryScannerTestCase;
use stdClass;
use WeakMap;

class WeakMapExporterTest extends MemoryScannerTestCase
{

    public function testGetProperties(): void
    {
        $object = new stdClass();
        $weakMap = new WeakMap();
        $weakMap[$object] = 1;

        $exporter = new WeakMapExporter();
        self::assertSame(['values' => [1]], $exporter->getProperties($weakMap));
    }

}
