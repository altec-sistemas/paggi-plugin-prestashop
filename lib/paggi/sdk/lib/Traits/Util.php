<?php

namespace Paggi\Traits;

use Paggi\PaggiException;

/**
 * Trait Util - Funtions util for the requests.
 */
trait Util
{
    /**
     * This method manage the response and return a exception or resposne.
     *
     * @param $responseCurl - Curl response
     *
     * @return string - Response json
     *
     * @throws PaggiException Exception
     */
    static protected function manageResponse($responseCurl)
    {
        $reflectedClass = get_called_class();
        $responseBody = $responseCurl->response;

        switch ($responseCurl->httpStatusCode) {
            case 200:
                if (array_key_exists('result', $responseBody) && array_key_exists('total', $responseBody)) {
                    $result = array();

                    foreach ($responseBody['result'] as $resultItem) {
                        array_push($result, new $reflectedClass($resultItem));
                    }

                    return array('result' => $result, 'total' => $responseBody['total']);
                }

                return new $reflectedClass($responseCurl->response);
            case 402:
                return new $reflectedClass($responseCurl->response);
            case 401:
                throw new PaggiException('Not a valid API key');
            case 410:
                throw new PaggiException(self::_getError($responseCurl));
            default:
                throw new PaggiException(self::_getError($responseCurl));
        }
    }

    /**
     * This method manage the Erros.
     *
     * @param $responseCurl
     *
     * @return array Array error
     */
    static protected function _getError($responseCurl)
    {
        //The original message error from API
        $originalError = json_decode($responseCurl->rawResponse, true);
        //HttpStatusCode
        $code = $responseCurl->httpStatusCode;
        //Some errors get null - Check it
        if (!is_null($originalError) && !empty($originalError)) {
            $paggiError = $originalError;
        } else {
            //Errors null
            $paggiError = array('error' => 'No Content', 'code' => $code);
        }

        return $paggiError;
    }
}
