Описание
------------------
Расширение для компрессии данных при чтении/записи в Redis
Использование
------------------
##### Для компрессии/декомпрессии данных в hash-структурах:
Необходимо в конфигурации баз данных(db.php):
* Заменить yii\redis\Connection на forz13\redisCompressedConnection\ConnectionWithHashCompression
* Указать compressor => forz13\redisCompressedConnection\GzipCompressor
* Указать уровень компрессии при записи => compressionLevel (1..9, default =6)
* Указать включить ли компрессию при записи => compressionOnWrite (1/0)

```
    'redis' => [
        'class'                    => ConnectionWithHashCompression::class,
        'compressor'               => [
            'class' => GzipCompressor::class,
            'compressionLevel' => 9
        ],
        'compressionOnWrite'       => 1
        'hostname'                 => getenv('REDIS_MAIN_HOST'),
        'port'                     => getenv('REDIS_MAIN_PORT'),
        'database'                 => getenv('REDIS_MAIN_DATABASE_WORKERS'),
        'password'                 => getenv('REDIS_MAIN_PASSWORD') ?: null,
        'connectionTimeout'        => getenv('REDIS_MAIN_CONNECT_TIMEOUT'),
        'dataTimeout'              => getenv('REDIS_MAIN_TIMEOUT'),
    ],
```

 
##### Для компрессии/декомпрессии данных в key/value структурах:
Необходимо в конфигурации баз данных(db.php):
* Заменить yii\redis\Connection на forz13\redisCompressedConnection\ConnectionWithKeyValueCompression
* Указать compressor => forz13\redisCompressedConnection\GzipCompressor
* Указать уровень компрессии при записи => compressionLevel (1..9, default =6)
* Указать включить ли компрессию при записи => compressionOnWrite (1/0)
```
    'redis' => [
        'class'                    => ConnectionWithKeyValueCompression::class,
        'compressor'               => [
            'class' => GzipCompressor::class,
            'compressionLevel' => 9
        ],
        'compressionOnWrite'       => 1,
        'hostname'                 => getenv('REDIS_MAIN_HOST'),
        'port'                     => getenv('REDIS_MAIN_PORT'),
        'database'                 => getenv('REDIS_MAIN_DATABASE_WORKERS'),
        'password'                 => getenv('REDIS_MAIN_PASSWORD') ?: null,
        'connectionTimeout'        => getenv('REDIS_MAIN_CONNECT_TIMEOUT'),
        'dataTimeout'              => getenv('REDIS_MAIN_TIMEOUT'),
    ],
```
