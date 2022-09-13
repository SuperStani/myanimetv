<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit95f9399c08e3aac93c5c313e89bfe61e
{
    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'superbot\\Telegram\\' => 18,
            'superbot\\Database\\' => 18,
            'superbot\\App\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'superbot\\Telegram\\' => 
        array (
            0 => __DIR__ . '/..' . '/superbot/Telegram/src',
        ),
        'superbot\\Database\\' => 
        array (
            0 => __DIR__ . '/..' . '/superbot/Database',
        ),
        'superbot\\App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'superbot\\App\\Config\\GeneralConfigs' => __DIR__ . '/../..' . '/app/Config/GeneralConfigs.php',
        'superbot\\App\\Controllers\\Controller' => __DIR__ . '/../..' . '/app/Controllers/Controller.php',
        'superbot\\Database\\DB' => __DIR__ . '/..' . '/superbot/Database/DB.php',
        'superbot\\Telegram\\Api' => __DIR__ . '/..' . '/superbot/Telegram/src/Api.php',
        'superbot\\Telegram\\Client' => __DIR__ . '/..' . '/superbot/Telegram/src/Client.php',
        'superbot\\Telegram\\Message' => __DIR__ . '/..' . '/superbot/Telegram/src/Message.php',
        'superbot\\Telegram\\Query' => __DIR__ . '/..' . '/superbot/Telegram/src/Query.php',
        'superbot\\Telegram\\Request' => __DIR__ . '/..' . '/superbot/Telegram/src/Request.php',
        'superbot\\Telegram\\Update' => __DIR__ . '/..' . '/superbot/Telegram/src/Update.php',
        'superbot\\Telegram\\User' => __DIR__ . '/..' . '/superbot/Telegram/src/User.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit95f9399c08e3aac93c5c313e89bfe61e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit95f9399c08e3aac93c5c313e89bfe61e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit95f9399c08e3aac93c5c313e89bfe61e::$classMap;

        }, null, ClassLoader::class);
    }
}