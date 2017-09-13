<?php

namespace Paggi;

/**
 * Class PaggiException.
 */
class PaggiException extends \Exception
{
    /**
     * PaggiException constructor.
     *
     * @param string $message Message Exception
     */
    public function __construct($message)
    {
        parent::__construct(json_encode($message), 0);
    }
}
