<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'n8n' => [
        'login_otp_url' => env('N8N_LOGIN_OTP_URL', 'https://n8n.fieldpass.com.my/webhook/login-otp'),
        'verify_otp_url' => env('N8N_VERIFY_OTP_URL', 'https://n8n.fieldpass.com.my/webhook/verify-otp'),
        'admin_invitation_url' => env('N8N_ADMIN_INVITATION_URL', 'https://n8n.fieldpass.com.my/webhook/admin-invitation'),
        'send_message_url' => env('N8N_SEND_MESSAGE_URL', 'https://n8n.fieldpass.com.my/webhook/send-message'),
    ],

    /*
    | Dev-only: entering this phone on admin login (Send OTP) logs in as superadmin without n8n OTP.
    | Enabled when APP_ENV=local, or when ADMIN_ALLOW_SUPERADMIN_OTP_BYPASS=true (use only on trusted hosts).
    */
    'admin' => [
        'superadmin_otp_bypass_phone' => env('ADMIN_SUPERADMIN_OTP_BYPASS_PHONE', '9999999999'),
        'allow_superadmin_otp_bypass' => env('ADMIN_ALLOW_SUPERADMIN_OTP_BYPASS'),
    ],

];
