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
  public function capture()
  {
    $rest = new RestClient();
    $curl = $rest->getCurl();
    $class = new \ReflectionClass(self::class);

    $idResource = get_object_vars($this)['id'];

    $curl->put($rest->getEndpoint($class->getShortName()) . '/'. $idResource. '/capture');

    return self::manageResponse($curl);
  }
}

?>
