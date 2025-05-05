<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\MemoryRootsProvider;

use ShipMonk\MemoryScanner\MemoryRootsProvider;
use function array_reverse;
use function count;
use function restore_exception_handler;
use function set_exception_handler;

final class ExceptionHandlerMemoryRootsProvider implements MemoryRootsProvider
{

    public function getRoots(): array
    {
        $roots = [];

        while (true) {
            $exceptionHandler = $this->getExceptionHandler();

            if ($exceptionHandler === null) {
                break;
            }

            $rootIndex = count($roots);
            $roots["exception handler #{$rootIndex}"] = $exceptionHandler;
            restore_exception_handler();
        }

        unset($exceptionHandler);

        foreach (array_reverse($roots) as $exceptionHandler) {
            set_exception_handler($exceptionHandler);
        }

        return $roots;
    }

    private function getExceptionHandler(): ?callable
    {
        $exceptionHandler = set_exception_handler(null);
        restore_exception_handler();

        return $exceptionHandler;
    }

}
