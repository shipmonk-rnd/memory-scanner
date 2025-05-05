<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner;

use Closure;
use WeakMap;
use function count;
use function gc_collect_cycles;
use function implode;
use function ksort;
use function spl_object_id;

final class ObjectDeallocationChecker
{

    public const UNKNOWN_ROOT = '__unknown__';

    /**
     * @var WeakMap<object, string>
     */
    private WeakMap $trackedObjects;

    /**
     * @var Closure(): MemoryScanner
     */
    private Closure $memoryScannerFactory;

    /**
     * @param ?Closure(): MemoryScanner $memoryScannerFactory
     */
    public function __construct(?Closure $memoryScannerFactory = null)
    {
        $this->trackedObjects = new WeakMap();
        $this->memoryScannerFactory = $memoryScannerFactory ?? MemoryScanner::create(...);
    }

    public function expectDeallocation(
        object $object,
        string $label,
    ): void
    {
        $this->trackedObjects[$object] = $label;
    }

    /**
     * @return array<string, array<string, list<ObjectReference>>>
     */
    public function checkDeallocations(): array
    {
        gc_collect_cycles();

        if (count($this->trackedObjects) === 0) {
            return [];
        }

        $memoryScanner = ($this->memoryScannerFactory)();
        $roots = $memoryScanner->findRoots();
        $objectReferences = $memoryScanner->findObjectReferences($roots);
        $causes = [];

        foreach ($this->trackedObjects as $trackedObject => $label) {
            if (!isset($objectReferences[$trackedObject])) {
                $root = self::UNKNOWN_ROOT;
                $causes[$root][$label] = [];

            } else {
                $rootReference = $memoryScanner->findRootReference($trackedObject, $objectReferences);
                $root = $rootReference[0]->path[0];
                $causes[$root][$label] = $rootReference;
            }
        }

        ksort($causes);
        return $causes;
    }

    /**
     * @param array<string, array<string, list<ObjectReference>>> $leaks
     */
    public function explainLeaks(array $leaks): string
    {
        $blocks = [];

        foreach ($leaks as $root => $leakedObjects) {
            $leakedObjectCount = count($leakedObjects);
            $lines = [];

            $lines[] = $root === self::UNKNOWN_ROOT
                ? "The following {$leakedObjectCount} objects could not be deallocated,\nbut the reason why is unknown:"
                : "The following {$leakedObjectCount} objects could not be deallocated,\nbecause they are referenced from '{$root}':";

            foreach ($leakedObjects as $objectLabel => $objectReferences) {
                $lines[] = "  Object '{$objectLabel}' is referenced thought the following path:'";

                foreach ($objectReferences as $objectReference) {
                    if ($objectReference->source !== null) {
                        $sourceLabel = $this->getSourceObjectLabel($objectReference);
                        $lines[] = "    -> {{$sourceLabel}}";
                    }

                    foreach ($objectReference->path as $segment) {
                        $lines[] = "    -> {$segment}";
                    }
                }
            }

            $blocks[] = implode("\n", $lines);
        }

        return implode("\n\n", $blocks);
    }

    private function getSourceObjectLabel(ObjectReference $objectReference): string
    {
        if (isset($this->trackedObjects[$objectReference->source])) {
            return $this->trackedObjects[$objectReference->source];
        }

        $objectId = spl_object_id($objectReference->source);
        return 'instance of ' . $objectReference->source::class . " #{$objectId}";
    }

}
