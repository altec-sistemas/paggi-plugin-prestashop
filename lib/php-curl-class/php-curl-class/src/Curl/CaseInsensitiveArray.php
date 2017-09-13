<?php

namespace Curl;

class CaseInsensitiveArray implements \ArrayAccess, \Countable, \Iterator
{
    /**
     * @var mixed[] Data storage with lower-case keys
     *
     * @see offsetSet()
     * @see offsetExists()
     * @see offsetUnset()
     * @see offsetGet()
     * @see count()
     * @see current()
     * @see next()
     * @see key()
     */
    private $data = array();

    /**
     * @var string[] case-Sensitive keys
     *
     * @see offsetSet()
     * @see offsetUnset()
     * @see key()
     */
    private $keys = array();

    /**
     * Construct.
     *
     * Allow creating either an empty Array, or convert an existing Array to a
     * Case-Insensitive Array.  (Caution: Data may be lost when converting Case-
     * Sensitive Arrays to Case-Insensitive Arrays)
     *
     * @param mixed[] $initial (optional) Existing Array to convert
     *
     * @return CaseInsensitiveArray
     */
    public function __construct(array $initial = null)
    {
        if (null !== $initial) {
            foreach ($initial as $key => $value) {
                $this->offsetSet($key, $value);
            }
        }
    }

    /**
     * Offset Set.
     *
     * Set data at a specified Offset.  Converts the offset to lower-case, and
     * stores the Case-Sensitive Offset and the Data at the lower-case indexes
     * in $this->keys and @this->data.
     *
     * @see https://secure.php.net/manual/en/arrayaccess.offseteset.php
     *
     * @param string $offset the offset to store the data at (case-insensitive)
     * @param mixed  $value  the data to store at the specified offset
     */
    public function offsetSet($offset, $value)
    {
        if (null === $offset) {
            $this->data[] = $value;
        } else {
            $offsetlower = strtolower($offset);
            $this->data[$offsetlower] = $value;
            $this->keys[$offsetlower] = $offset;
        }
    }

    /**
     * Offset Exists.
     *
     * Checks if the Offset exists in data storage.  The index is looked up with
     * the lower-case version of the provided offset.
     *
     * @see https://secure.php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param string $offset Offset to check
     *
     * @return bool if the offset exists
     */
    public function offsetExists($offset)
    {
        return (bool) array_key_exists(strtolower($offset), $this->data);
    }

    /**
     * Offset Unset.
     *
     * Unsets the specified offset. Converts the provided offset to lowercase,
     * and unsets the Case-Sensitive Key, as well as the stored data.
     *
     * @see https://secure.php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param string $offset the offset to unset
     */
    public function offsetUnset($offset)
    {
        $offsetlower = strtolower($offset);
        unset($this->data[$offsetlower]);
        unset($this->keys[$offsetlower]);
    }

    /**
     * Offset Get.
     *
     * Return the stored data at the provided offset. The offset is converted to
     * lowercase and the lookup is done on the Data store directly.
     *
     * @see https://secure.php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param string $offset offset to lookup
     *
     * @return mixed the data stored at the offset
     */
    public function offsetGet($offset)
    {
        $offsetlower = strtolower($offset);

        return isset($this->data[$offsetlower]) ? $this->data[$offsetlower] : null;
    }

    /**
     * Count.
     *
     * @see https://secure.php.net/manual/en/countable.count.php
     *
     * @param void
     *
     * @return int the number of elements stored in the Array
     */
    public function count()
    {
        return (int) count($this->data);
    }

    /**
     * Current.
     *
     * @see https://secure.php.net/manual/en/iterator.current.php
     *
     * @param void
     *
     * @return mixed data at the current position
     */
    public function current()
    {
        return current($this->data);
    }

    /**
     * Next.
     *
     * @see https://secure.php.net/manual/en/iterator.next.php
     *
     * @param void
     */
    public function next()
    {
        next($this->data);
    }

    /**
     * Key.
     *
     * @see https://secure.php.net/manual/en/iterator.key.php
     *
     * @param void
     *
     * @return mixed case-Sensitive key at current position
     */
    public function key()
    {
        $key = key($this->data);

        return isset($this->keys[$key]) ? $this->keys[$key] : $key;
    }

    /**
     * Valid.
     *
     * @see https://secure.php.net/manual/en/iterator.valid.php
     *
     * @return bool if the current position is valid
     */
    public function valid()
    {
        return (bool) !(null === key($this->data));
    }

    /**
     * Rewind.
     *
     * @see https://secure.php.net/manual/en/iterator.rewind.php
     *
     * @param void
     */
    public function rewind()
    {
        reset($this->data);
    }
}
