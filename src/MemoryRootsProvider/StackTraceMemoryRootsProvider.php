<?php declare(strict_types = 1);

namespace ShipMonk\MemoryScanner\MemoryRootsProvider;

use ShipMonk\MemoryScanner\MemoryRootsProvider;
use function debug_backtrace;
use function extension_loaded;
use function function_exists;
use function in_array;
use function xdebug_get_function_stack;
use function xdebug_info;

final class StackTraceMemoryRootsProvider implements MemoryRootsProvider
{

    public function getRoots(): array
    {
        if (
            extension_loaded('xdebug')
            && function_exists('xdebug_get_function_stack')
            && in_array('develop', xdebug_info('mode'), strict: true)
        ) {
            return xdebug_get_function_stack(['local_vars' => true, 'params_as_values' => true]);
        }

        return debug_backtrace();
    }

}
