<?php

namespace Curl;

class ArrayUtil
{
    /**
     * Is Array Assoc.
     *
     * @param  $array
     *
     * @return bool
     */
    public static function is_array_assoc($array)
    {
        return (bool) count(array_filter(array_keys($array), 'is_string'));
    }

    /**
     * Is Array Multidim.
     *
     * @param  $array
     *
     * @return bool
     */
    public static function is_array_multidim($array)
    {
        if (!is_array($array)) {
            return false;
        }

        return (bool) count(array_filter($array, 'is_array'));
    }

    /**
     * Array Flatten Multidim.
     *
     * @param  $array
     * @param  $prefix
     *
     * @return array
     */
    public static function array_flatten_multidim($array, $prefix = false)
    {
        $return = array();
        if (is_array($array) || is_object($array)) {
            if (empty($array)) {
                $return[$prefix] = '';
            } else {
                foreach ($array as $key => $value) {
                    if (is_scalar($value)) {
                        if ($prefix) {
                            $return[$prefix.'['.$key.']'] = $value;
                        } else {
                            $return[$key] = $value;
                        }
                    } else {
                        if ($value instanceof \CURLFile) {
                            $return[$key] = $value;
                        } else {
                            $return = array_merge(
                                $return,
                                self::array_flatten_multidim(
                                    $value,
                                    $prefix ? $prefix.'['.$key.']' : $key
                                )
                            );
                        }
                    }
                }
            }
        } elseif (null === $array) {
            $return[$prefix] = $array;
        }

        return $return;
    }
}
