<?php

namespace Paggi;

/**
 * Class Paggi manage all resoucers and API values
 * @package Paggi
 */
class Paggi
{
    static private $isStaging; //Enviroment staging
    static private $token; //Token value

    static public function setApiKey($api_key = ""){
        if (is_null($api_key) || strcmp($api_key, "") == 0) {
            throw new PaggiException(array("type" => "Unauthorized", "message" => "The parameter 'token' cannot be a null or empty string"));
        }
        self::$token = $api_key;
    }

    static public function setStaging($staging = false){
        self::$isStaging = $staging;
    }

    /**
     * Get a token value
     * @return Token value
     */
    static public function getToken()
    {
        return self::$token;
    }

    /**
     * Get if the environment is staging
     * @return bool
     */
    static public function isStaging()
    {
        return self::$isStaging;
    }

}

?>
