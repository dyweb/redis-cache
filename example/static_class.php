<?php
// Composer Autoloader
require __DIR__ . '/../vendor/autoload.php';

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

// Store permanmently
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
$value = RedisCache::get('key', function () {
    return 'default';
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