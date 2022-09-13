<?php

namespace superbot\Telegram;
use superbot\App\Config\GeneralConfigs;

class Request{
    public static function get(string $method, array $args = []): \stdClass
    {
        $curl = curl_init();
        $endpoint = "https://api.telegram.org/bot".GeneralConfigs::get("bot_token")."/";
        curl_setopt_array($curl, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_FORBID_REUSE   => true,
            CURLOPT_HEADER         => false,
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_CONNECTTIMEOUT => 2,
            CURLOPT_HTTPHEADER     => ["Connection: Keep-Alive", "Keep-Alive: 120"],

            CURLOPT_URL        => $endpoint . $method,
            CURLOPT_POSTFIELDS => empty($args) ? null : $args,
        ]);
        $resultCurl = curl_exec($curl);
        if ($resultCurl === false) {
            $arr = [
                "ok"          => false,
                "error_code"  => curl_errno($curl),
                "description" => curl_error($curl),
                "curl_error"  => true
            ];

            $resultCurl = json_encode($arr);
        }

        $resultJson = json_decode($resultCurl);
        if ($resultJson === null) {
            $arr = [
                "ok"          => false,
                "error_code"  => json_last_error(),
                "description" => json_last_error_msg(),
                "json_error"  => true
            ];
            $resultJson = json_decode(json_encode($arr));
        }

        return $resultJson;
    }
    
}