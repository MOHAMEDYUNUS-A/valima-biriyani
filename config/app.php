<?php

use App\Helpers\Env;

return [
    'name' => Env::get('APP_NAME', 'Valima Biriyani'),
    'env' => Env::get('APP_ENV', 'production'),
    'debug' => (bool)Env::get('APP_DEBUG', false),
    'url' => Env::get('APP_URL', 'http://localhost:8000'),
    'key' => Env::get('APP_KEY', ''),
    'timezone' => Env::get('TIMEZONE', 'Asia/Kolkata'),
    'session' => [
        'name' => Env::get('SESSION_NAME', 'valima_session'),
        'lifetime' => (int)Env::get('SESSION_LIFETIME', 7200),
    ],
    'restaurant' => [
        'name' => Env::get('RESTAURANT_NAME', 'Valima Biriyani'),
        'tagline' => Env::get('RESTAURANT_TAGLINE', 'Authentic Traditional Dum Biriyani & More'),
        'phone' => Env::get('RESTAURANT_PHONE', '+91 98765 43210'),
        'whatsapp' => Env::get('RESTAURANT_WHATSAPP', '+919876543210'),
        'address' => Env::get('RESTAURANT_ADDRESS', 'Grand Southern Trunk Rd, Chennai, Tamil Nadu 600045'),
        'gstin' => Env::get('RESTAURANT_GSTIN', ''),
        'currency_symbol' => Env::get('RESTAURANT_CURRENCY_SYMBOL', '₹'),
        'currency_code' => Env::get('RESTAURANT_CURRENCY_CODE', 'INR'),
    ],
    'loyalty' => [
        'points_per_order' => (int)Env::get('LOYALTY_POINTS_PER_ORDER', 10),
        'point_value_inr' => (float)Env::get('LOYALTY_POINT_VALUE_INR', 1.00),
    ],
    'integrations' => [
        'razorpay' => [
            'enabled' => (bool)Env::get('RAZORPAY_ENABLED', false),
            'key_id' => Env::get('RAZORPAY_KEY_ID', ''),
            'key_secret' => Env::get('RAZORPAY_KEY_SECRET', ''),
            'webhook_secret' => Env::get('RAZORPAY_WEBHOOK_SECRET', ''),
        ],
        'whatsapp' => [
            'api_enabled' => (bool)Env::get('WHATSAPP_API_ENABLED', false),
            'phone_number_id' => Env::get('WHATSAPP_PHONE_NUMBER_ID', ''),
            'access_token' => Env::get('WHATSAPP_ACCESS_TOKEN', ''),
            'verify_token' => Env::get('WHATSAPP_WEBHOOK_VERIFY_TOKEN', ''),
        ],
        'make' => [
            'enabled' => (bool)Env::get('MAKE_WEBHOOK_ENABLED', false),
            'url' => Env::get('MAKE_WEBHOOK_URL', ''),
            'secret' => Env::get('MAKE_WEBHOOK_SECRET', ''),
        ],
    ],
];
