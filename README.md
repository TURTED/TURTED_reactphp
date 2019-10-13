TURTED Server
=

Implementation of TURTED protocol v2 in PHP using [ReactPHP](https://reactphp.org/)

How to use
-

```
cd example
php server.php
```

Server will be running on local default port 19195.

Open the `example/index.html` file in multiple browsers/tabs and you will be able to send messages to all tabs



Configuration
-

Server start parameters.
Any of the parameters can be skipped/omitted so they will fall back to default values

```
$server = new TurtedServer(
    [               
        // Port to run server on
        'port' => 19195,                              

         // expects a callable to return a username for an incoming client request
        'user_resolver' => [$userResolver, 'getUserForRequest'],

        // in case the server is not running on its own port/base directory
        'base_url' => 'sse', 

        // For CORS Allow-Origin header
        'allow_origin' => '*',                                       
        // 'allow_origin' > ['http://127.0.0.1:8080'],
        // 'allow_origin' > ['https://www.example.com'],

        // callable to check auth data for push requests
        'auth_handler' => function($auth) { return 's3cr3t'===$auth['password']; }, 
    ]
);
```

@TODO documentation `user_resolver`

@TODO documentation `forwarding nginx, base_url`

@TODO documentation `auth_handler`

