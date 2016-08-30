<?php
// Composer Autoloader
require __DIR__ . '/../vendor/autoload.php';

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
$value = $repository->get('key', function () {
    return 'default';
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