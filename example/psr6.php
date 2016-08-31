<?php

// Composer Autoloader
require __DIR__.'/../vendor/autoload.php';

use Dy\Cache\Psr\Pool;
use Dy\Cache\RedisRepository;

// Initialize the repository
$repository = new RedisRepository(array(
    'connection' => array(
        'client' => 'predis',
        'schema' => 'tcp',
        'host'   => '127.0.0.1',
        'port'   => 6379,
    ),
    'namespace' => array(
        'name'         => 'dy:test',
        'key_set_name' => 'keys',
        'lazy_record'  => true,
    ),
    'memory_cache' => false,
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
