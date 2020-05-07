Histrix API Client
==================

This is an API Client for Histrix Framework

usage:

```php

$histrix = new \Histrix\API\Client(
        "https://histrix-api-server/api/db/somewhat",
        'username',
        'password',
        'client_credentials',
        [
            'key', 'secret'
        ]
);

$clients = $histrix->get( "/app/end/point/target", [ '_limit' => '0' ] );

```
