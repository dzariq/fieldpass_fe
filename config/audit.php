<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auditing Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration file is used to set the settings for the OwenIt Auditing
    | package. It controls the behavior of the auditing system in your application.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Default User Resolver
    |--------------------------------------------------------------------------
    |
    | If you are using the default Laravel authentication, the `UserResolver`
    | will resolve the authenticated user for audit records. You can change this
    | to a custom `UserResolver` if needed.
    |
    */
    'user_resolver' => \OwenIt\Auditing\Resolvers\UserResolver::class,

    'resolver' => [
        'admin'       => App\Models\Admin::class,
        'player'       => App\Models\Player::class,
        'ip_address' => OwenIt\Auditing\Resolvers\IpAddressResolver::class,
        'user_agent' => OwenIt\Auditing\Resolvers\UserAgentResolver::class,
        'url'        => OwenIt\Auditing\Resolvers\UrlResolver::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Auditable Models
    |--------------------------------------------------------------------------
    |
    | You can define which models should be auditable. By default, all models
    | that implement the `OwenIt\Auditing\Contracts\Auditable` contract will be
    | audited.
    |
    */
    'audit_models' => [
        // Add your models that should be auditable here
        // Example:
        // App\Models\Post::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Table Name
    |--------------------------------------------------------------------------
    |
    | You can change the name of the table used to store audit logs. If you're
    | using a custom table name, make sure the table exists in your database.
    |
    */
    'table' => 'audits',

    /*
    |--------------------------------------------------------------------------
    | Event Listener
    |--------------------------------------------------------------------------
    |
    | This defines the event listener that is fired when an audit is created.
    | You can change it to a custom listener if you need additional logic when
    | an audit is created.
    |
    */
    'event' => \OwenIt\Auditing\Events\Audited::class,

    /*
    |--------------------------------------------------------------------------
    | Audit Log Format
    |--------------------------------------------------------------------------
    |
    | You can control how the audit logs are stored in the database. By default,
    | all changes are stored in JSON format. You can change this format if needed.
    |
    */
    'log_format' => 'json',

    /*
    |--------------------------------------------------------------------------
    | Audit Log Exclude Attributes
    |--------------------------------------------------------------------------
    |
    | If you wish to exclude certain attributes from being logged (e.g., sensitive
    | data), you can specify them here. The attributes will not be included in
    | the audit logs.
    |
    */
    'exclude' => [
        // Example:
        // 'password', 'api_token'
    ],

    /*
    |--------------------------------------------------------------------------
    | Enable or Disable Auditing
    |--------------------------------------------------------------------------
    |
    | If you'd like to globally disable auditing for your application, you can
    | set this to false.
    |
    */
    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Audit Driver
    |--------------------------------------------------------------------------
    |
    | The driver used for storing the audit records. You can choose between 'database'
    | (default) or 'file' if you want to store audit logs as flat files instead of
    | in the database.
    |
    */
    'driver' => 'database',  // 'file' or 'database'

    /*
    |--------------------------------------------------------------------------
    | Database Connection
    |--------------------------------------------------------------------------
    |
    | If you're using a custom database connection for the audit logs, you can specify
    | it here. This allows you to store your audit logs in a different database.
    |
    */
    'connection' => null, // Use null to use the default connection

    /*
    |--------------------------------------------------------------------------
    | Adders for Custom Attributes
    |--------------------------------------------------------------------------
    |
    | You can add custom attributes to the audit logs using adders. If you need to
    | include extra information that isn't part of the model itself, you can
    | define those here.
    |
    */
    'adders' => [
        // Example:
        // \App\Adders\CustomAdder::class
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit for Specific Events
    |--------------------------------------------------------------------------
    |
    | You can define which events should trigger an audit. For example, you might
    | want to audit only create and update events, and not deletes.
    |
    */
    'events' => [
        'created',
        'updated',
        'deleted',
    ],

    /*
    |--------------------------------------------------------------------------
    | Max Audit History
    |--------------------------------------------------------------------------
    |
    | If you want to limit the number of audit logs for each model, you can specify
    | a maximum number of records here. Once the limit is reached, the oldest records
    | will be deleted.
    |
    */
    'max_audit_history' => 1000,  // Set to 0 for unlimited history

    /*
    |--------------------------------------------------------------------------
    | Log in Debug Mode
    |--------------------------------------------------------------------------
    |
    | You can enable logging to help debug the auditing process. This will log
    | audit-related information to your Laravel log file.
    |
    */
    'debug' => false,

];
