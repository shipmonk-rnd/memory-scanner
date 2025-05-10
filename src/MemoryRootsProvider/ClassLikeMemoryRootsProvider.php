<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\MemoryRootsProvider;

use ReflectionClass;
use ShipMonk\MemoryScanner\MemoryRootsProvider;
use function get_declared_classes;
use const PHP_VERSION_ID;

final class ClassLikeMemoryRootsProvider implements MemoryRootsProvider
{

    public function getRoots(): array
    {
        $roots = [];

        foreach (get_declared_classes() as $className) {
            $classReflection = new ReflectionClass($className);

            foreach ($classReflection->getProperties() as $propertyReflection) {
                if ($propertyReflection->isStatic() && $propertyReflection->isInitialized()) {
                    $propertyName = $propertyReflection->getName();
                    $propertyValue = $propertyReflection->getValue();
                    $roots["static property {$className}::\${$propertyName}"] = $propertyValue;
                }

                if (PHP_VERSION_ID >= 8_04_00) {
                    foreach ($propertyReflection->getHooks() as $hookReflection) {
                        $hookName = $hookReflection->getName(); // e.g. '$foo::get'

                        foreach ($hookReflection->getStaticVariables() as $staticVariableName => $staticVariableValue) {
                            $roots["static variable \${$staticVariableName} inside property hook {$className}::{$hookName}()"] = $staticVariableValue;
                        }
                    }
                }
            }

            foreach ($classReflection->getMethods() as $methodReflection) {
                $methodName = $methodReflection->getName();

                foreach ($methodReflection->getStaticVariables() as $staticVariableName => $staticVariableValue) {
                    $roots["static variable \${$staticVariableName} inside method {$className}::{$methodName}()"] = $staticVariableValue;
                }
            }
        }

        return $roots;
    }

}
