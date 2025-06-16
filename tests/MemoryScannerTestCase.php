<?php declare(strict_types = 1);

namespace ShipMonkTests\MemoryScanner;

use PHPUnit\Framework\Constraint\Exception as ExceptionConstraint;
use PHPUnit\Framework\TestCase;
use Throwable;
use function dirname;
use function file_get_contents;
use function file_put_contents;
use function getenv;
use function is_file;
use function mkdir;
use function strlen;
use const LOCK_EX;

abstract class MemoryScannerTestCase extends TestCase
{

    /**
     * @param class-string<T> $type
     * @param callable(): mixed $cb
     *
     * @template T of Throwable
     *
     * @param-immediately-invoked-callable $cb
     */
    protected static function assertException(
        string $type,
        ?string $message,
        callable $cb,
    ): void
    {
        try {
            $cb();
            self::assertThat(null, new ExceptionConstraint($type));

        } catch (Throwable $e) {
            self::assertThat($e, new ExceptionConstraint($type));

            if ($message !== null) {
                self::assertStringMatchesFormat($message, $e->getMessage());
            }
        }
    }

    protected static function assertSnapshot(
        string $snapshotPath,
        string $actual,
    ): void
    {
        if (is_file($snapshotPath) && getenv('UPDATE_SNAPSHOTS') === false) {
            $expected = file_get_contents($snapshotPath);
            self::assertSame($expected, $actual);

        } elseif (getenv('CI') === false) {
            @mkdir(dirname($snapshotPath), recursive: true);
            self::assertSame(strlen($actual), file_put_contents($snapshotPath, $actual, LOCK_EX));

        } else {
            self::fail("Snapshot file {$snapshotPath} does not exist. Run tests locally to generate it.");
        }
    }

}
