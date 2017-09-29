<?php

namespace Paggi\Traits;

use \Paggi\RestClient;

/**
 * Trait Update - Update a resource
 * @package Paggi\Traits
 */
trait Update
{
    /**
     * PUT METHOD
     * @param $rest - The RestClient object
     * @param $id - ID resource
     * @param $params - Resource params
     * @return mixed - Exception or response
     */
    public function update($params)
    {
        $rest = new RestClient();
        $curl = $rest->getCurl();
        $class = new \ReflectionClass(self::class);

        $idResource = get_object_vars($this)['id'];

        $curl->put($rest->getEndpoint($class->getShortName()) . "/" . $idResource, json_encode($params));

        return self::manageResponse($curl);
    }
}
