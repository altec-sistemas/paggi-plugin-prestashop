<?php

namespace Paggi;

//Curl for manager the HTTP requests
use Curl\Curl;
use Doctrine\Common\Inflector\Inflector;

/**
 * Class RestClient - This class manager the requests.
 */
class RestClient
{
    private $curl;
    const BASE_STAGING = 'https://staging-online.paggi.com/api/v4/'; //STAGING
    const BASE_PRODUCTION = 'https://online.paggi.com/api/v4/'; //PRODUCTION
    private $endPoint;

    /**
     * RestClient constructor.
     */
    public function __construct()
    {
        //Get the Enviroment
        $this->endPoint = $this->getEnviroment(Paggi::isStaging());

        //Instance the curl
        $this->curl = new Curl();
        $this->curl->setBasicAuthentication(Paggi::getToken(), '');
        $this->curl->setDefaultJsonDecoder($assoc = true);
        $this->curl->setHeader('Content-Type', 'application/json; charset=utf-8');
        $this->curl->setDefaultTimeout();

        if (array_key_exists('X_FORWARDED_FOR', $_SERVER)) {
            $this->curl->setHeader('X-Forwarded-For', $_SERVER['X_FORWARDED_FOR']);
        } else {
            $this->curl->setHeader('X-Forwarded-For', $_SERVER['REMOTE_ADDR']);
        }
    }

    /**
     * Return the Environment.
     *
     * @param $isStaging
     *
     * @return string API Environment
     */
    private function getEnviroment($isStaging = false)
    {
        if (true == $isStaging) {
            return self::BASE_STAGING;
        } else {
            return self::BASE_PRODUCTION;
        }
    }

    /**
     * Get the Endpoint [banks - bank-accounts - customer - cards - charges].
     *
     * @param $resource - The resource used [banks - bank-accounts - customer - cards - charges]
     *
     * @return string [banks - bank-accounts - customer - cards - charges]
     */
    public function getEndpoint($resource)
    {
        $entity = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', Inflector::pluralize($resource)));

        return strtolower($this->endPoint.$entity);
    }

    /**
     * Return the curl for manage the HTTP Requests.
     *
     * @return Curl
     */
    public function getCurl()
    {
        return $this->curl;
    }
}
