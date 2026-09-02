<?php

return [
    'admin' => [
        'email' => env('ICLEH_ADMIN_EMAIL', 'admin@icleh.test'),
        'password' => env('ICLEH_ADMIN_PASSWORD'),
    ],

    'payment' => [
        'bank_name' => env('ICLEH_PAYMENT_BANK', 'Bank Transfer Manual'),
        'account_number' => env('ICLEH_PAYMENT_ACCOUNT', 'Configure in .env'),
        'account_name' => env('ICLEH_PAYMENT_ACCOUNT_NAME', 'Faculty of Law UNTAG Semarang'),
    ],
];
