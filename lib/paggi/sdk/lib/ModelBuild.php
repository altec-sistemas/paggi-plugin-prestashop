<?php

namespace Paggi;

trait ModelBuild
{
    //key_exists - property_exists - array_key_exists - in_array (values)

    /**
     * This method get the response values and set it in to the parameters class.
     * $this -> is a class that call it.
     *
     * @param $properties values from Json response
     */
    protected function buildObject($properties)
    {
        foreach ($properties as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }
}
