<?php

namespace Paggi\Traits;

use \Paggi\RestClient;

/**
 * Trait FindAll - Find all resources
 * @package Paggi\Traits
 */
trait FindAll
{
    /**
     * GET METHOD
     * @param $rest - The RestClient object
     * @param $query_params - QueryParams for filter and pagination
     * @return mixed - Exception or response
     */
    public static function findAll($query_params = [])
    {
        $rest = new RestClient();
        $curl = $rest->getCurl();
        $class = new \ReflectionClass(self::class);

        $curl->get($rest->getEndpoint($class->getShortName()), $query_params);

        return self::manageResponse($curl);
    }
}
