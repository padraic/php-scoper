<?php

namespace Webmozart\PhpScoper\Util;

/**
 * @copyright  Copyright (c) 2015 by Nikita Popov
 * @license    https://github.com/nikic/TypeUtil/blob/master/LICENSE New BSD License
 */
class MutableString
{
    /**
     * @var string
     */
    private $string;

    /**
     * @var array[pos, len, newString]
     */
    private $modifications = [];

    public function __construct($string)
    {
        $this->string = $string;
    }

    /**
     * @param $pos
     * @param $newString
     */
    public function insert($pos, $newString)
    {
        $this->modifications[] = [$pos, 0, $newString];
    }

    /**
     * @param $pos
     * @param $len
     */
    public function remove($pos, $len)
    {
        $this->modifications[] = [$pos, $len, ''];
    }

    /**
     * @param $str
     * @param $startPos
     *
     * @return bool|int
     */
    public function indexOf($str, $startPos)
    {
        return strpos($this->string, $str, $startPos);
    }

    /**
     * @return string
     */
    public function getOrigString()
    {
        return $this->string;
    }

    /**
     * @return string
     */
    public function getModifiedString()
    {
        // Sort by position
        usort($this->modifications, function($a, $b) {
            return $a[0] <=> $b[0];
        });

        $result = '';
        $startPos = 0;
        foreach ($this->modifications as list($pos, $len, $newString)) {
            $result .= substr($this->string, $startPos, $pos - $startPos);
            $result .= $newString;
            $startPos = $pos + $len;
        }
        $result .= substr($this->string, $startPos);
        return $result;
    }
}