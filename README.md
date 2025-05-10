# ShipMonk PHP Memory Scanner

A lightweight PHP library for analyzing memory usage and finding memory leaks in PHP applications.

This package provides tools to:

1. Scan PHP memory for objects and their references
2. Track object allocations and detect objects that aren't properly deallocated
3. Find reference paths from global roots to leaked objects
4. Generate detailed explanations of why objects aren't being deallocated

The core functionality relies on traversing the PHP object graph from various memory roots (like superglobals, functions, classes, etc.) to detect what's keeping your objects in memory.

Perfect for debugging memory leaks in long-running PHP applications, especially when working with frameworks or code that maintains complex object relationships.

## Installation:

```sh
composer require shipmonk/memory-scanner
```

## Contributing
- Check your code by `composer check`
- Autofix coding-style by `composer fix:cs`
- All functionality must be tested
