<?php

namespace Myth\LaravelTools\Utilities;

use Illuminate\Support\Carbon;

class QrTag
{
    /**
     * @var
     */
    protected $tag;

    /**
     * @var
     */
    protected $value;

    /**
     * @param $tag
     * @param $value
     */
    public function __construct($tag, $value)
    {
        $this->tag = $tag;
        $this->value = $value;
    }

    /**
     * @param $tag
     * @param $value
     *
     * @return static
     */
    public static function array($tag, $value): self
    {
        return new static($tag, $value);
    }

    /**
     * @param $array
     *
     * @return string
     */
    public static function toBase64(array $array): string
    {
        $string = base64_encode(implode('', array_map(function ($tag) {
            return (string) $tag;
        }, $array)));
        //dd($string);
        return $string;
    }

    /**
     * Make tags as base64.
     * @param string|null $name
     * @param string|null $number
     * @param string|Carbon|null $date
     * @param string|float|null $amount
     * @param string|float|null $tax
     * @return string
     */
    public static function make(?string $name, ?string $number, string | Carbon | null $date, string | float | null $amount, string | float | null $tax)
    {
        return static::toBase64([
            static::array(1, (string) $name ?: ''),
            static::array(2, (string) $number ?: ''),
            static::array(3, Carbon::make($date ?: '')->format("Y-m-d H:i:s")),
            static::array(4, (string) $amount ?: ''),
            static::array(5, (string) $tax ?: ''),
        ]);
    }

    /**
     * @return string Returns a string representing the encoded TLV data structure.
     */
    public function __toString()
    {
        $value = (string) $this->getValue();

        return $this->toHex($this->getTag()).$this->toHex($this->getLength()).($value);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getTag()
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
}
