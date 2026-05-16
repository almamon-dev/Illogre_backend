<?php

return [
    'api_key' => env('SHOPIFY_API_KEY'),
    'api_secret' => env('SHOPIFY_API_SECRET'),
    'app_name' => env('SHOPIFY_APP_NAME', 'Illogre App'),
    'scopes' => env('SHOPIFY_SCOPES', 'read_products,write_products,read_customers'),
    'app_host' => env('SHOPIFY_APP_HOST'),
];
