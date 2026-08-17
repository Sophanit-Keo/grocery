<?php

return [
    'proxies' => env('TRUSTED_PROXIES', env('VERCEL') ? '*' : null),
];
