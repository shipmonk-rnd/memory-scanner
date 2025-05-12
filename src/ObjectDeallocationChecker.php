<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner;

use Closure;
use WeakMap;
use function array_map;
use function array_sum;
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
    public function checkDeallocations(bool $verbose = false): array
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
                continue;
            }

            $ignoreReferencesFrom = $verbose ? null : $this->trackedObjects;
            $rootReference = $memoryScanner->findRootReference($trackedObject, $objectReferences, $ignoreReferencesFrom);

            if ($rootReference === null) {
                continue; // secondary leak (an object leaked only because another tracked object leaked)
            }

            $root = (string) $rootReference[0]->path[0];
            $causes[$root][$label] = $rootReference;
        }

        ksort($causes);
        return $causes;
    }

    /**
     * @param array<string, array<string, list<ObjectReference>>> $objectLeakCauses
     */
    public function explainLeaks(array $objectLeakCauses): string
    {
        $blocks = [];

        $totalLeakedObjectCount = array_sum(array_map(count(...), $objectLeakCauses));
        $maybePlural = $totalLeakedObjectCount > 1 ? 'objects are' : 'object is';
        $blocks[] = "Expected all tracked objects to be deallocated, but total of {$totalLeakedObjectCount} {$maybePlural} still in memory.";

        foreach ($objectLeakCauses as $root => $leakedObjects) {
            $lines = [];
            $leakedObjectCount = count($leakedObjects);
            $maybePlural1 = $leakedObjectCount > 1 ? 's' : '';
            $maybePlural2 = $leakedObjectCount > 1 ? 'they are' : 'it is';
            $linePrefix = "  The following {$leakedObjectCount} object{$maybePlural1} could not be deallocated";

            if ($root === self::UNKNOWN_ROOT) {
                $lines[] = "{$linePrefix}, but this library was unable to figure out why. This could be, because it is referenced from a shutdown handler.";

                foreach ($leakedObjects as $objectLabel => $objectReferences) {
                    $lines[] = "    - '{$objectLabel}'";
                }

                $lines[] = '';

            } else {
                $lines[] = "{$linePrefix}, because {$maybePlural2} referenced from '{$root}':";

                foreach ($leakedObjects as $objectLabel => $objectReferences) {
                    $lines[] = "    Object '{$objectLabel}' is referenced thought the following path:";

                    foreach ($objectReferences as $objectReference) {
                        if ($objectReference->source !== null) {
                            $sourceLabel = $this->getSourceObjectLabel($objectReference->source);
                            $lines[] = "       = {{$sourceLabel}}";
                        }

                        foreach ($objectReference->path as $segment) {
                            $lines[] = "      -> {$segment}";
                        }
                    }

                    $lines[] = "       = {{$objectLabel}}";
                    $lines[] = '';
                }
            }

            $blocks[] = implode("\n", $lines);
        }

        return implode("\n\n", $blocks);
    }

    private function getSourceObjectLabel(object $object): string
    {
        if (isset($this->trackedObjects[$object])) {
            return $this->trackedObjects[$object];
        }

        $objectId = spl_object_id($object);
        return $object::class . " #{$objectId}";
    }

}
