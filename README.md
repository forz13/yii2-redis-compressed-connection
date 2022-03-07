### Redis gzip data compression extension

------------------
### Configuration:
In the database configuration (db.php):
* Replace yii\redis\Connection with 
   - For key/value data store use forz13\redisCompressedConnection\ConnectionWithKeyValueCompression 
   - For hash data store use forz13\redisCompressedConnection\ConnectionWithHashCompression
* Specify compressor => forz13\redisCompressedConnection\GzipCompressor
* Specify compression level  => compressionLevel (1..9, default =6)
* Specify whether to enable compression when writing => compressionOnWrite (1/0)

```
    'redis' => [
        'class' => ConnectionWithHashCompression::class,
        'compressor' => [
            'class' => GzipCompressor::class,
            'compressionLevel' => 9
        ],
        'compressionOnWrite' => 1
        'hostname' => 'localhost',
        'port' => 6379,
        'database' => 1,
        ....
    ],
```

### Supported commands:

  #### ConnectionWithKeyValueCompression:
```
    - GET
    - SET
```
  #### ConnectionWithHashCompression:
```
    - HSET
    - HGET
    - HVALS
    - HGETALL
    - HMGET
```

 
