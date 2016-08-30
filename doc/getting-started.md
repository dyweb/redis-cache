# Getting Started

## Through static methods

```php
<?php
// Composer Autoloader
require __DIR__ . '/vendor/autoload.php';

use Dy\Cache\RedisCache;

// Set up configurations
RedisCache::config(array(
   'connection' => array(
       'client' => 'predis',
       'schema' => 'tcp',
       'host' => '127.0.0.1',
       'port' => 6379,
   ),
   'namespace' => array(
       'name' => 'dy:test',
       'key_set_name' => 'keys',
       'lazy_record' => true
   ),
   'memory_cache' => false
));

// Keep the cache item for 1 minute
RedisCache::put('key', 'value', 1);

// Keep the cache item for 1 second
RedisCache::set('key', 'value', 1 / 60);

// Store non-string values
RedisCache::set('key', array('a', 'b', 'c'), 1);

// Chain storing
RedisCache::set('key1', 'value1', 1)
          ->set('key2', 'value2', 1)
          ->set('key3', 'value3', 1);

// Store permanently
RedisCache::forever('key', 'value');

// Fetch the value
$value = RedisCache::get('key');

// Check the existence
if (RedisCache::has('key')) {
    // ...
}

// Fetch the value with default when the key does not exist
$value = RedisCache::get('key', 'default');

// Closure is also supported
$db = new Db();
$value = RedisCache::get('key', function () use ($db) {
    return $db->fetch('SELECT name FROM users LIMIT 1')[0];
});

// Fetch the value and delete it
$value = RedisCache::pull('key');
var_dump(RedisCache::has('key'));      // false

// Delete a key
$boolean = RedisCache::del('key');

// Use as a counter
RedisCache::increment('counter');
RedisCache::increment('counter', 2);
var_dump(RedisCache::get('counter'));  // 3

RedisCache::decrement('counter');
RedisCache::decrement('counter', 1);
var_dump(RedisCache::get('counter'));  // 1

// Fetch all the keys
$keys = RedisCache::getAllKeys();

// Clear all the keys in the current namespace
RedisCache::clearAll();

// Change to another namespace
RedisCache::setNamespace('dy:other');

```

## Through a new instance

```php
<?php
// Composer Autoloader
require __DIR__ . '/vendor/autoload.php';

use Dy\Cache\RedisRepository;

$repository = new RedisRepository(array(
   'connection' => array(
       'client' => 'predis',
       'schema' => 'tcp',
       'host' => '127.0.0.1',
       'port' => 6379,
   ),
   'namespace' => array(
       'name' => 'dy:test',
       'key_set_name' => 'keys',
       'lazy_record' => true
   ),
   'memory_cache' => false
));

// Keep the cache item for 1 minute
$repository->put('key', 'value', 1);

// Keep the cache item for 1 second
$repository->set('key', 'value', 1 / 60);

// Store non-string values
$repository->set('key', array('a', 'b', 'c'), 1);

// Chain storing
$repository->set('key1', 'value1', 1)
           ->set('key2', 'value2', 1)
           ->set('key3', 'value3', 1);

// Store permanently
$repository->forever('key', 'value');

// Fetch the value
$value = $repository->get('key');

// Check the existence
if ($repository->has('key')) {
    // ...
}

// Fetch the value with default when the key does not exist
$value = $repository->get('key', 'default');

// Closure is also supported
$db = new Db();
$value = $repository->get('key', function () use ($db) {
    return $db->fetch('SELECT name FROM users LIMIT 1')[0];
});

// Fetch the value and delete it
$value = $repository->pull('key');
var_dump($repository->has('key'));      // false

// Delete a key
$boolean = $repository->del('key');

// Use as a counter
$repository->increment('counter');
$repository->increment('counter', 2);
var_dump($repository->get('counter'));  // 3

$repository->decrement('counter');
$repository->decrement('counter', 1);
var_dump($repository->get('counter'));  // 1

// Fetch all the keys
$keys = $repository->getAllKeys();

// Clear all the keys in the current namespace
$repository->clearAll();

// Change to another namespace
$repository->setNamespace('dy:other');

```

## Through PSR-6

```php
<?php
// Composer Autoloader
require __DIR__ . '/vendor/autoload.php';

use Dy\Cache\RedisRepository;
use Dy\Cache\Psr\Item;
use Dy\Cache\Psr\Pool;

// Initialize the repository
$repository = new RedisRepository(array(
   'connection' => array(
       'client' => 'predis',
       'schema' => 'tcp',
       'host' => '127.0.0.1',
       'port' => 6379,
   ),
   'namespace' => array(
       'name' => 'dy:test',
       'key_set_name' => 'keys',
       'lazy_record' => true
   ),
   'memory_cache' => false
));

// Create a pool
$pool = new Pool($repository);

// Store a key
$item = $pool->getItem('key');
$pool->save($item->set('value')->expiresAfter(1));           // Expires after 1 second

$item2 = $pool->getItem('key');
$interval = new \DateInterval('PT1M5S');
$pool->save($item2->set('value')->expiresAfter($interval));  // Expires after 1 minute, 5 seconds

$item3 = $pool->getItem('key3');
$date = new \DateTime('2017-01-01 00:00:00');
$pool->save($item3->set('value')->expiresAt($date));         // Expires at the given date

// Store permanently
$item4 = $pool->getItem('key4');
$pool->save($item4->set('value'));

// Fetch a key
$item = $pool->getItem('key');
$boolean = $item->isHit();
$boolean2 = $pool->hasItem('key');

$value = $item->get();

// Get multiple items
$items = $pool->getItems(array('key1', 'key2', 'key3'));

// Delete keys
$pool->deleteItem('key');
$pool->deleteItems(array('key1', 'key2', 'key3'));

```
