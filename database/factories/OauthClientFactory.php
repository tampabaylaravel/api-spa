<?php

use Laravel\Passport\Client;
use Faker\Generator as Faker;

$factory->define(Client::class, function (Faker $faker) {
    return [
        'name' => 'Test Oauth Client',
        'secret' => 'rqsbFEbrpNfWHlKbwRYgnE8HHLQtkeHyq33Dp5bc',
        'redirect' => 'http://localhost',
        'personal_access_client' => 0,
        'password_client' => 0,
        'revoked' => 0,
    ];
});

$factory->state(Client::class, 'password', [
    'password_client' => 1,
]);
