# RedisCache

[![License](https://poser.pugx.org/dyweb/redis-cache/license)](https://packagist.org/packages/dyweb/redis-cache)
[![StyleCI](https://styleci.io/repos/38742941/shield?style=flat)](https://styleci.io/repos/38742941)
[![Build Status](https://travis-ci.org/dyweb/redis-cache.svg)](https://travis-ci.org/dyweb/redis-cache)
[![codecov](https://codecov.io/gh/dyweb/redis-cache/branch/master/graph/badge.svg)](https://codecov.io/gh/dyweb/redis-cache)
[![Latest Stable Version](https://poser.pugx.org/dyweb/redis-cache/v/stable)](https://packagist.org/packages/dyweb/redis-cache)
[![HHVM Status](http://hhvm.h4cc.de/badge/dyweb/redis-cache.svg?style=flat)](http://hhvm.h4cc.de/package/dyweb/redis-cache)

RedisCache is a simple cache library with key namespaces, Redis support,
and PSR-6 implementation. To keep it simple, it only provides the simplest
interface and brings with no dependencies (except PSR-6 interfaces). Different
from other complex and redundant cache libraries, RedisCache is just tiny and
powerful.

## Features

- Support both [Predis](https://github.com/nrk/predis) and [PhpRedis](https://github.com/phpredis/phpredis)
- Support PSR-6
- Store and manage keys in different namespaces
- Use with fluent interface
- Utilize lazy recording for better performance
- Utilize additional in-memory cache to reduce I/O
- No dependencies except PSR-6 interfaces

## Requirements

- PHP 5.3 or newer
- Redis 2.8 or newer

## Installation

RedisCache is [Composer](https://getcomposer.org/) and [PSR-4](http://www.php-fig.org/psr/psr-4/)
ready. To install it, just run the following command:

```bash
composer require dyweb/redis-cache
```

## Getting Started

Please check the [examples](doc/getting-started.md) here.

## Configuration

Please check the [configuration](doc/configuration.md) doc.

## Development

### Contributing

Contributing to this project is highly appreciated through merge requests
for new features or bug fixes, bug reporting or just suggestions. Please
follow the PSR-2 coding standard when contributing, and ensure that all your
contributions are not against our coding style and integrated tests.

### Testing

Follow these commands to configure the necessary environment and run
checks and tests:

```bash
composer install

# run tests
./run-check.sh
```

The latest version of [PHPUnit](https://phpunit.de/) is recommended for
testing.

## License

The code for this project is distributed under the terms of the MIT License.
(See [LICENSE](LICENSE))