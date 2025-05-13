# ShipMonk PHP Memory Scanner

A lightweight PHP library for analyzing memory usage and finding memory leaks in PHP applications.

This package provides tools to:

1. Scan PHP memory for objects and their references
2. Track object allocations and detect objects that aren't properly deallocated
3. Find reference paths from global roots to leaked objects
4. Generate detailed explanations of why objects aren't being deallocated

The core functionality relies on traversing the PHP object graph from various memory roots (like superglobals, functions, classes, etc.) to detect what's keeping your objects in memory.

Perfect for debugging memory leaks in long-running PHP applications, especially when working with frameworks or code that maintains complex object relationships.


## Usage with Symfony & PHPUnit

In any test that extends `KernelTestCase`, you can use the trait `ObjectDeallocationCheckerKernelTestCaseTrait` to enable memory leak detection. This requires PHPUnit 11 or higher.

```php
use ShipMonk\MemoryScanner\Bridge\PHPUnit\ObjectDeallocationCheckerKernelTestCaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomepageControllerTest extends WebTestCase
{
    use ObjectDeallocationCheckerKernelTestCaseTrait;

    public function testHomepageOk(): void
    {
        self::createClient()->request('GET', '/');
        self::assertResponseIsSuccessful();
    }
}
```


## Manual Usage

You can also use the library manually in any PHP script. Here's a simple example:

```php
$objectDeallocationChecker = new \ShipMonk\MemoryScanner\ObjectDeallocationChecker();
$objectDeallocationChecker->expectDeallocation($kernel, 'kernel');

$kernel->shutdown();
unset($kernel);

$memoryLeaks = $objectDeallocationChecker->checkDeallocations();

if (count($memoryLeaks) > 0) {
    Assert::fail($objectDeallocationChecker->explainLeaks($memoryLeaks));
}
```


## Installation:

```sh
composer require shipmonk/memory-scanner
```

## Contributing
- Check your code by `composer check`
- Autofix coding-style by `composer fix:cs`
- All functionality must be tested
