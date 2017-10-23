<?php

namespace Paggi\Traits;

use \Paggi\RestClient;

/**
 * Trait Create - Create/Create a new resource
 * @package Paggi\Traits
 * */
trait Create
{
    /**
     * POST METHOD
     *
     * @param $params Resource paramns
     * @throws PaggiException Representation of HTTP error code
     * @return mixed Object representing created entity
     */
    static public function create($params)
    {
        $rest = new RestClient();
        $curl = $rest->getCurl();
        $class = new \ReflectionClass(self::class);

        $curl->post($rest->getEndpoint($class->getShortName()), json_encode($params));

        //return $class->newInstanceArgs($curl->response);
        return self::manageResponse($curl);
    }
}
