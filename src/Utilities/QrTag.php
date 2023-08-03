<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Utilities;

class QrTag
{
    protected int $tag;

    protected string $value;

    public function __construct($tag, $value)
    {
        $this->tag = $tag;
        $this->value = (string) $value;
    }

    /**
     * @return string Returns a string representing the encoded TLV data structure.
     */
    public function __toString()
    {
        return $this->toHex($this->getTag()).$this->toHex($this->getLength()).($this->getValue());
    }

    /**
     * To convert the string value to hex.
     *
     * @param $value
     *
     * @return false|string
     */
    protected function toHex($value)
    {
        return pack("H*", sprintf("%02X", $value));
    }

    /**
     * @return int
     */
    public function getTag(): int
    {
        return $this->tag;
    }

    /**
     * its important to get the number of bytes of a string instated of number of characters
     *
     * @return false|int
     */
    public function getLength()
    {
        return strlen($this->value);
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
