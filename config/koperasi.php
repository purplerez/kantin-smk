<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Maksimal Nominal Transaksi Koperasi
    |--------------------------------------------------------------------------
    |
    | Batas nilai per transaksi untuk kasir dan checkout koperasi.
    | null = tanpa batas (default).
    |
    */
    'max_transaction_amount' => env('KOPERASI_MAX_TRANSACTION_AMOUNT', null),
];

