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
  static function cancel($id)
  {
    $rest = new RestClient();
    $curl = $rest->getCurl();
    $class = new \ReflectionClass(self::class);

    $curl->put($rest->getEndpoint($class->getShortName()). '/'. $id .'/cancel');

    return self::manageResponse($curl);
  }
}

?>
