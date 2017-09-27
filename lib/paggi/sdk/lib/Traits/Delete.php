<?php

namespace Paggi\Traits;

use \Paggi\RestClient;

/**
 * Trait Delete - Delete a resource
 * @package Paggi\Traits
 */
trait Delete
{
    /**
     * DELETE METHOD
     * @param $rest - The RestClient object
     * @param $id - ID resource
     * @return mixed - Exception or Response
     */
    public function delete()
    {
        $rest = new RestClient();
        $curl = $rest->getCurl();
        $class = new \ReflectionClass(self::class);

        $idResource = get_object_vars($this)['id'];

        $curl->delete($rest->getEndpoint($class->getShortName()) . "/" . $idResource);

        return true;
    }
}
