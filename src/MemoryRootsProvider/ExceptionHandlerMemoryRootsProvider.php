<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\MemoryRootsProvider;

use ShipMonk\MemoryScanner\MemoryRootsProvider;
use function count;
use function restore_exception_handler;
use function set_exception_handler;

final class ExceptionHandlerMemoryRootsProvider implements MemoryRootsProvider
{

    /**
     * @return list<callable>
     */
    public function getRoots(): array
    {
        $roots = [];

        while (true) {
            $exceptionHandler = $this->getExceptionHandler();

            if ($exceptionHandler === null) {
                break;
            }

            $roots[] = $exceptionHandler;
            restore_exception_handler();
        }

        for ($i = count($roots) - 1; $i >= 0; $i--) {
            set_exception_handler($roots[$i]);
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
