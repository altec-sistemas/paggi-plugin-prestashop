<?php

namespace Paggi\Traits;

use \Paggi\RestClient;

/**
 * Trait FindById - Find a resource by ID
 * @package Paggi\Traits
 */
trait FindById
{
    /**
     * @param $rest - The RestClient object
     * @param $id - Resouce ID
     * @return mixed - Exception or a Response
     */
    public static function findById($id)
    {
        $rest = new RestClient();
        $curl = $rest->getCurl();
        $class = new \ReflectionClass(self::class); //Clas information

        $curl->get($rest->getEndpoint($class->getShortName()) . "/" . $id);

        return self::manageResponse($curl);
    }

}
