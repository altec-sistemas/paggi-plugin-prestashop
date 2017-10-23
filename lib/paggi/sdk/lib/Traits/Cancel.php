<?php

namespace Paggi\Traits;

use \Paggi\RestClient;

/**
 * Trait Cancel
 * Use it to manually cancel any charge or pre authorization that wasn't already cancelled.
 * A charge can be cancelled up to 180 days after its confirmation.
 * @package Paggi\Traits
 */
trait Cancel
{
  /**
   * @param $rest - The RestClient object
   * @param $id - Resouce ID
   * @return mixed - Exception or a Response
   */
  public function cancel()
  {
    $rest = new RestClient();
    $curl = $rest->getCurl();
    $class = new \ReflectionClass(self::class);

    $idResource = get_object_vars($this)['id'];

    $curl->put($rest->getEndpoint($class->getShortName()). '/'. $idResource .'/cancel');

    return self::manageResponse($curl);
  }
}

?>
