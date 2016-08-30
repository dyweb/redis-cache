# Configuration

Thw configuration is as follows:

```php
<?php
$config = array(
    'connection' => array(
        'client' => 'predis',
        'schema' => 'tcp',
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => 'secret'
    ),
    'namespace' => array(
        'name' => 'dy:test',
        'key_set_name' => 'keys',
        'lazy_record' => true
    ),
    'memory_cache' => false
);

// Init with RedisCache::config() or new RedisRepository()
// ....
```

- `connection.client`: **(required)** Client library to use. `predis`([Predis](https://github.com/nrk/predis))
    or `redis`([PhpRedis](https://github.com/phpredis/phpredis)). The 
    Predis library is required to connect with `predis`, or the PhpRedis
    extension is required to connect with `redis`: 
- `connection.schema`: **(required)** Protocol to use. `tcp` or `unix`. 
- `connection.host`: **(required for `tcp`)** Host of the redis server.
- `connection.port`: **(required for `tcp`)** Port of the redis server.
- `connection.path`: **(required for `unix`)** Path of the unix socket.
- `connection.timeout`: Timeout of the connection in seconds.
- `connection.password`: Password of the redis server.
- `namespace.name`: **(required)** Identifier of the namespace. Used for
    the prefix of all the keys in this namespace.
- `namespace.key_set_name`: **(required)** Key name of the set to store
    the meta of all the keys in all namespaces. Leave a blank string to
    disable this feature.
- `namespace.lazy_recoed`: **(required)** Only effective when `key_set_name`
    is not blank. Enabling this feature will put off key meta updating
    to the time when the session is closed or `setNamespace` is called.
- `memory_cache`: **(required)** Switch to memory caching. Enabling this
    feature will make the same key fetched only once from redis server 
    and stored in the memory, which reduces I/O but may cause phantom
    reads.
    