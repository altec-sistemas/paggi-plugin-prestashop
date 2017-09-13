<?php

namespace Paggi;

use Paggi\Traits\Util;

/**
 * Trait Create - Create a new resource.
 * */
abstract class Entity
{
    use Util;

    /**
     * @param $params Array Entity properties
     * */
    public function __construct(array $params)
    {
        foreach ($params as $property => $value) {
            $this->{$property} = $value;
        }
    }
}
