<?php
return [
    'base_url' => env('SIB_BASE_URL', 'https://sib.umsu.ac.ir'),

    'timeout' => (int)env('SIB_TIMEOUT', 30),

    'connect_timeout' => (int)env('SIB_CONNECT_TIMEOUT', 10),

    'client_type' => env('SIB_CLIENT_TYPE', 'User'),

    'auth_method' => env('SIB_AUTH_METHOD', 'Password'),
];

