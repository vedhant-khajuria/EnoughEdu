<?php
return [
    'app' => [
        'name' => 'EnoughEdu',
        'url' => 'https://enoughedu.vedhant.in',
        'timezone' => 'Asia/Kolkata',
        'env' => 'production',
        'debug' => false,
    ],
    'database' => [
        'host' => 'localhost',
        'port' => '3306',
        'name' => 'cpaneluser_enoughedu',
        'user' => 'cpaneluser_edu',
        'password' => 'CHANGE_ME',
        'charset' => 'utf8mb4',
    ],
    'mail' => [
        'from_email' => 'hello@vedhant.in',
        'from_name' => 'EnoughEdu',
        'reply_to' => 'hello@vedhant.in',
    ],
    'admin_security' => [
        'otp_email' => getenv('ENOUGHEDU_ADMIN_OTP_EMAIL') ?: 'hello@vedhant.in',
        'otp_ttl_seconds' => 600,
        'otp_max_attempts' => 5,
        'otp_resend_cooldown' => 60,
    ],
    'razorpay' => [
        'key_id' => getenv('ENOUGHEDU_RAZORPAY_KEY_ID') ?: 'YOUR_RAZORPAY_KEY_ID',
        'key_secret' => getenv('ENOUGHEDU_RAZORPAY_KEY_SECRET') ?: 'YOUR_RAZORPAY_KEY_SECRET',
        'webhook_secret' =>
            getenv('ENOUGHEDU_RAZORPAY_WEBHOOK_SECRET') ?: 'YOUR_RAZORPAY_WEBHOOK_SECRET',
        'api_base' => 'https://api.razorpay.com/v1',
        'checkout_script' => 'https://checkout.razorpay.com/v1/checkout.js',
        'currency' => 'INR',
        'timeout' => 25,
    ],
    'ai' => [
        'provider' => 'Google Gemini',
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions',
        'api_key' => getenv('ENOUGHEDU_GEMINI_API_KEY') ?: '',
        'model' => getenv('ENOUGHEDU_GEMINI_MODEL') ?: '',
        'timeout' => 45,
    ],
    'google_oauth' => [
        'client_id' => getenv('ENOUGHEDU_GOOGLE_CLIENT_ID') ?: '',
        'client_secret' => getenv('ENOUGHEDU_GOOGLE_CLIENT_SECRET') ?: '',
        'redirect_uri' => 'https://enoughedu.vedhant.in/auth/google/callback',
        'authorization_endpoint' => 'https://accounts.google.com/o/oauth2/v2/auth',
        'token_endpoint' => 'https://oauth2.googleapis.com/token',
        'userinfo_endpoint' => 'https://openidconnect.googleapis.com/v1/userinfo',
        'timeout' => 15,
    ],
    'security' => [
        'session_name' => 'enoughedu_portfolio_session',
        'cookie_secure' => true,
        'session_lifetime' => 2592000,
    ],
];
