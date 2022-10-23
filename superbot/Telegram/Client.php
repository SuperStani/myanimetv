<?php

namespace superbot\Telegram;
use CURLFile;

class Client extends Api
{
    /*
     * @param string $path Path of file
     * @return \CURLFile of $path
     */

    public static function inputFile(string $path): \CURLFile
    {
        $path = realpath($path);

        return new CURLFile($path);
    }


    /*
     * Make var_export() and send it in the actual chat_id
     *
     * @param int|string $chat_id for the target chat
     * @param mixed,... $var unlimited optional variable to send
     *
     * @return bool true if can send message, otherwise false
     */

    public static function debug(...$vars): bool
    {
        foreach ($vars as $debug) {
            $str = var_export($debug, true);
            $array_str = str_split($str, 4050);

            foreach ($array_str as $value) {
                $result = static::sendMessage(406343901, "Debug:" . PHP_EOL . $value);
                if ($result->ok === false) {
                    return false;
                }
            }
        }

        return true;
    }

}
