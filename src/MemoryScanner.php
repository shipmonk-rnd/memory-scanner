<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner;

use ReflectionClass;
use ReflectionReference;
use ShipMonk\MemoryScanner\InternalObjectExporter\ClosureExporter;
use ShipMonk\MemoryScanner\InternalObjectExporter\ReflectionFiberExporter;
use ShipMonk\MemoryScanner\InternalObjectExporter\ReflectionFunctionExporter;
use ShipMonk\MemoryScanner\InternalObjectExporter\ReflectionGeneratorExporter;
use ShipMonk\MemoryScanner\MemoryRootsProvider\AutoloadMemoryRootsProvider;
use ShipMonk\MemoryScanner\MemoryRootsProvider\ClassLikeMemoryRootsProvider;
use ShipMonk\MemoryScanner\MemoryRootsProvider\ConstantMemoryRootsProvider;
use ShipMonk\MemoryScanner\MemoryRootsProvider\ErrorHandlerMemoryRootsProvider;
use ShipMonk\MemoryScanner\MemoryRootsProvider\ExceptionHandlerMemoryRootsProvider;
use ShipMonk\MemoryScanner\MemoryRootsProvider\FunctionMemoryRootsProvider;
use ShipMonk\MemoryScanner\MemoryRootsProvider\OutputBufferingHandlerMemoryRootsProvider;
use ShipMonk\MemoryScanner\MemoryRootsProvider\SignalHandlerMemoryRootsProvider;
use ShipMonk\MemoryScanner\MemoryRootsProvider\StackTraceMemoryRootsProvider;
use ShipMonk\MemoryScanner\MemoryRootsProvider\StreamContextMemoryRootsProvider;
use ShipMonk\MemoryScanner\MemoryRootsProvider\SuperGlobalMemoryRootsProvider;
use SplQueue;
use WeakMap;
use function get_mangled_object_vars;
use function is_a;
use function is_array;
use function is_object;
use function spl_object_id;
use function strrpos;
use function substr;

/**
 * Known limitations:
 *  - callbacks registered with register_shutdown_function() are not tracked
 *  - local variables on the stack are tracked only if xdebug is enabled and 'develop' mode is set
 */
final class MemoryScanner
{

    /**
     * @var list<MemoryRootsProvider>
     */
    private array $memoryRootsProvider = [];

    /**
     * @var list<InternalObjectExporter<*>>
     */
    private array $internalObjectExporters = [];

    /**
     * @internal use {@see MemoryScanner::create()} instead
     */
    public function __construct()
    {
    }

    public static function create(): self
    {
        $memoryScanner = new self();

        $memoryScanner->registerMemoryRootsProvider(new AutoloadMemoryRootsProvider());
        $memoryScanner->registerMemoryRootsProvider(new ClassLikeMemoryRootsProvider());
        $memoryScanner->registerMemoryRootsProvider(new ConstantMemoryRootsProvider());
        $memoryScanner->registerMemoryRootsProvider(new ErrorHandlerMemoryRootsProvider());
        $memoryScanner->registerMemoryRootsProvider(new ExceptionHandlerMemoryRootsProvider());
        $memoryScanner->registerMemoryRootsProvider(new FunctionMemoryRootsProvider());
        $memoryScanner->registerMemoryRootsProvider(new OutputBufferingHandlerMemoryRootsProvider());
        $memoryScanner->registerMemoryRootsProvider(new SignalHandlerMemoryRootsProvider());
        $memoryScanner->registerMemoryRootsProvider(new StackTraceMemoryRootsProvider());
        $memoryScanner->registerMemoryRootsProvider(new StreamContextMemoryRootsProvider());
        $memoryScanner->registerMemoryRootsProvider(new SuperGlobalMemoryRootsProvider());

        $memoryScanner->registerInternalObjectExporter(new ClosureExporter());
        $memoryScanner->registerInternalObjectExporter(new ReflectionFiberExporter());
        $memoryScanner->registerInternalObjectExporter(new ReflectionFunctionExporter());
        $memoryScanner->registerInternalObjectExporter(new ReflectionGeneratorExporter());

        return $memoryScanner;
    }

    public function registerMemoryRootsProvider(MemoryRootsProvider $provider): void
    {
        $this->memoryRootsProvider[] = $provider;
    }

    /**
     * @param InternalObjectExporter<*> $exporter
     */
    public function registerInternalObjectExporter(InternalObjectExporter $exporter): void
    {
        $this->internalObjectExporters[] = $exporter;
    }

    /**
     * Returns all values that are directly reachable from the global scope.
     *
     * @return array<string, mixed>
     */
    public function findRoots(): array
    {
        $roots = [];

        foreach ($this->memoryRootsProvider as $provider) {
            foreach ($provider->getRoots() as $rootKey => $rootValue) {
                $roots[$rootKey] = $rootValue;
            }
        }

        return $roots;
    }

    /**
     * Returns for all reachable objects a list of references to them.
     *
     * @param array<string, mixed> $memoryRoots
     * @return WeakMap<object, list<ObjectReference>>
     */
    public function findObjectReferences(array $memoryRoots): WeakMap
    {
        /** @var array<string, true> $visitedReferences */
        $visitedReferences = [];

        /** @var WeakMap<object, list<ObjectReference>> $objectReferences */
        $objectReferences = new WeakMap();

        /** @var SplQueue<array{mixed, ?object, list<string|int>}> $queue */
        $queue = new SplQueue();
        $queue->enqueue([$memoryRoots, null, []]);

        while (!$queue->isEmpty()) {
            [$item, $source, $path] = $queue->dequeue();

            if (is_array($item)) {
                foreach ($item as $key => $value) {
                    $refId = ReflectionReference::fromArrayElement($item, $key)?->getId();

                    if ($refId !== null) {
                        if (!isset($visitedReferences[$refId])) {
                            $visitedReferences[$refId] = true;

                        } else {
                            continue;
                        }
                    }

                    $queue->enqueue([$value, $source, [...$path, $key]]);
                }

            } elseif (is_object($item)) {
                $objectReference = new ObjectReference($source, $path);

                if (isset($objectReferences[$item])) {
                    $objectReferences[$item][] = $objectReference;
                    continue;
                }

                $objectReferences[$item] = [$objectReference];
                $queue->enqueue([$this->exportObject($item), $item, []]);
            }
        }

        return $objectReferences;
    }

    /**
     * Returns the shortest object reference sequence from the root to the given object,
     * that does not pass though any object in the $ignoreReferencesFrom map.
     *
     * @param WeakMap<object, list<ObjectReference>> $objectReferences
     * @param WeakMap<object, string>|null $ignoreReferencesFrom
     * @return non-empty-list<ObjectReference>|null
     */
    public function findRootReference(
        object $object,
        WeakMap $objectReferences,
        ?WeakMap $ignoreReferencesFrom = null,
    ): ?array
    {
        $visitedObjects = [];

        /** @var SplQueue<array{object, list<ObjectReference>}> $queue */
        $queue = new SplQueue();
        $queue->enqueue([$object, []]);

        while (!$queue->isEmpty()) {
            [$object, $referencePath] = $queue->dequeue();
            $objectId = spl_object_id($object);

            if (isset($visitedObjects[$objectId])) {
                continue;
            }

            $visitedObjects[$objectId] = true;

            foreach ($objectReferences[$object] ?? [] as $objectReference) {
                if ($objectReference->source === null) {
                    return [$objectReference, ...$referencePath];
                }

                if ($ignoreReferencesFrom !== null && isset($ignoreReferencesFrom[$objectReference->source])) {
                    continue;
                }

                $queue->enqueue([$objectReference->source, [$objectReference, ...$referencePath]]);
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private function exportObject(object $object): array
    {
        $mangledObjectProperties = get_mangled_object_vars($object); // same as (array), but ignores cast overloads
        $properties = [];

        foreach ($mangledObjectProperties as $key => $value) {
            $nullByteOffset = strrpos($key, "\x00");

            $propertyLabel = $nullByteOffset === false
                ? $key
                : substr($key, $nullByteOffset + 1);

            $properties['$' . $propertyLabel] = $value;
        }

        if ($this->isObjectInternal(new ReflectionClass($object))) {
            foreach ($this->internalObjectExporters as $exporter) {
                if (is_a($object, $exporter->getClassName(), allow_string: true)) {
                    $properties += $exporter->getProperties($object); // @phpstan-ignore argument.type
                }
            }
        }

        return $properties;
    }

    /**
     * @param ReflectionClass<object> $classReflection
     */
    private function isObjectInternal(ReflectionClass $classReflection): bool
    {
        return $classReflection->isInternal()
            || ($classReflection->getParentClass() !== false && $this->isObjectInternal($classReflection->getParentClass()));
    }

}
