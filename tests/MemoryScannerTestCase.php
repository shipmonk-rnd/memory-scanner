<?php declare(strict_types = 1);

namespace ShipMonkTests\MemoryScanner;

use PHPUnit\Framework\Constraint\Exception as ExceptionConstraint;
use PHPUnit\Framework\TestCase;
use Throwable;

abstract class MemoryScannerTestCase extends TestCase
{

    /**
     * @template T of Throwable
     * @param class-string<T> $type
     * @param callable(): mixed $cb
     * @param-immediately-invoked-callable $cb
     */
    protected static function assertException(string $type, ?string $message, callable $cb): void
    {
        try {
            $cb();
            self::assertThat(null, new ExceptionConstraint($type)); // @phpstan-ignore new.internalClass

        } catch (Throwable $e) {
            self::assertThat($e, new ExceptionConstraint($type)); // @phpstan-ignore new.internalClass

            if ($message !== null) {
                self::assertStringMatchesFormat($message, $e->getMessage());
            }
        }
    }

}
