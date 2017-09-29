<?php

namespace Paggi;

/**
 * Class Paggi manage all resoucers and API values
 * @package Paggi
 */
class Paggi
{
    private static $isStaging; //Enviroment staging
    private static $token; //Token value

    public static function setApiKey($api_key = "")
    {
        if (is_null($api_key) || strcmp($api_key, "") == 0) {
            throw new PaggiException(array("type" => "Unauthorized", "message" => "The parameter 'token' cannot be a null or empty string"));
        }
        self::$token = $api_key;
    }

    public static function setStaging($staging = false)
    {
        self::$isStaging = $staging;
    }

    /**
     * Get a token value
     * @return Token value
     */
    public static function getToken()
    {
        return self::$token;
    }

    /**
     * Get if the environment is staging
     * @return bool
     */
    public static function isStaging()
    {
        return self::$isStaging;
    }
}
