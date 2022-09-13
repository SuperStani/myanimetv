<?php

namespace superbot\App\Config;

class GeneralConfigs{
    private static $configs = [
        "dbhost" => "localhost",
        "dbname" => "myanimetv2",
        "dbuser" => "admin",
        "dbpassword" => "@Naruto96",
        "bot_token" => "5372846548:AAGxB8Ye2dqhHvnxQos8X4S8jLaU4MrIOG8",
        "domain" => "https://myanimetv.org/bots/myanimetv/",
        "webapp" => "https://webapp.myanimetv.org/",
        "mainChannel" => 406343901,
        "postersChannel" => 21332,
        "episodesChannel" => -1001701898672
    ];
    public static function get($item) {
        return self::$configs[$item];
    }
}