<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\MemoryRootsProvider;

use ReflectionFunction;
use ShipMonk\MemoryScanner\MemoryRootsProvider;
use function get_defined_functions;

final class FunctionMemoryRootsProvider implements MemoryRootsProvider
{

    public function getRoots(): array
    {
        $roots = [];

        foreach (get_defined_functions()['user'] as $functionName) {
            $functionReflection = new ReflectionFunction($functionName);

            foreach ($functionReflection->getStaticVariables() as $staticVariableName => $staticVariableValue) {
                $roots["static variable \${$staticVariableName} inside function {$functionName}()"] = $staticVariableValue;
            }
        }

        return $roots;
    }

}
