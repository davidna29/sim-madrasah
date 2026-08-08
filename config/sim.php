<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Initial Administrator
    |--------------------------------------------------------------------------
    |
    | Digunakan hanya untuk membuat akun administrator pertama
    | pada saat instalasi awal aplikasi.
    |
    */

    'initial_admin' => [
        'name' => env(
            'SIM_INITIAL_ADMIN_NAME',
            'Administrator SIM Madrasah'
        ),

        'username' => env(
            'SIM_INITIAL_ADMIN_USERNAME',
            'superadmin'
        ),

        'email' => env(
            'SIM_INITIAL_ADMIN_EMAIL'
        ),

        'password' => env(
            'SIM_INITIAL_ADMIN_PASSWORD'
        ),
    ],

];
