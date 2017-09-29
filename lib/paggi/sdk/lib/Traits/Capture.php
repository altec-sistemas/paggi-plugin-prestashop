<?php

namespace Paggi\Traits;

use \Paggi\RestClient;

/**
 * Trait Capure
 * Use it to confirm a pre authorization.
 * Pre authorizations that are not captured up to 7 days will be automatically cancelled by the acquirer.
 * @package Paggi\Traits
 */
trait Capture
{
    /**
     * @param $rest - The RestClient object
     * @param $id - Resouce ID
     * @return mixed - Exception or a Response
     */
    public static function capture($id)
    {
        $rest = new RestClient();
        $curl = $rest->getCurl();
        $class = new \ReflectionClass(self::class);

        $curl->put($rest->getEndpoint($class->getShortName()) . '/'. $id. '/capture');

        return self::manageResponse($curl);
    }
}
