<?php

/*
 * This file is part of the webmozart/php-scoper package.
 *
 * (c) Bernhard Schussek <bschussek@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Webmozart\PhpScoper\Tests\Util;

use PHPUnit\Framework\TestCase;
use Webmozart\PhpScoper\Util\MutableString;

/**
 * @author Padraic Brady <padraic.brady@gmail.com>
 */
class MutableStringTest extends TestCase
{
    /**
     * @var string
     */
    private static $string = "foo bar baz";

    /**
     * @var MutableString
     */
    private $mutable = null;

    public function setUp()
    {
        $this->mutable = new MutableString(self::$string);
    }

    public function testGettingOriginalString()
    {
        $this->assertSame(self::$string, $this->mutable->getOrigString());
    }

    public function testInsertionAtStartOfString()
    {
        $this->mutable->insert(0, 'kong ');
        $this->assertSame('kong foo bar baz', $this->mutable->getModifiedString());
    }

    public function testInsertionAtEndOfString()
    {
        $this->mutable->insert(11, ' kong');
        $this->assertSame('foo bar baz kong', $this->mutable->getModifiedString());
    }

    public function testInsertionFromEndOfStringUsingNegativeOffset()
    {
        $this->mutable->insert(-3, 'kong ');
        $this->assertSame('foo bar kong baz', $this->mutable->getModifiedString());
    }

    public function testInsertionAfterEndOfStringOccursAtEndOfString()
    {
        $this->mutable->insert(12, ' kong');
        $this->assertSame('foo bar baz kong', $this->mutable->getModifiedString());
    }

    public function testRemoveAtStartOfString()
    {
        $this->mutable->remove(0, 4);
        $this->assertSame('bar baz', $this->mutable->getModifiedString());
    }

    public function testRemoveAtEndOfString()
    {
        $this->mutable->remove(7, 4);
        $this->assertSame('foo bar', $this->mutable->getModifiedString());
    }

    public function testRemoveFromEndOfStringUsingNegativeOffset()
    {
        $this->markTestIncomplete(
            'Underlying unit appears to handle this counterintuitively. '
            .'Removes as expected, but is appending original string also?'
        );
        $this->mutable->remove(-4, 4);
        $this->assertSame('foo bar', $this->mutable->getModifiedString2());
    }

    public function testRemoveOperatesFromOffsetEvenIfRemovalLengthExtendedPastStringEnd()
    {
        $this->mutable->remove(8, 4);
        $this->assertSame('foo bar ', $this->mutable->getModifiedString());
    }

    public function testIndexOfStartOfString()
    {
        $this->assertSame(0, $this->mutable->indexOf('foo', 0));
    }

    public function testIndexOfEndOfString()
    {
        $this->assertSame(8, $this->mutable->indexOf('baz', 8));
    }

    public function testIndexOfEndOfStringUsingNegativeOffset()
    {
        $this->assertSame(8, $this->mutable->indexOf('baz', -3));
    }

    public function testIndexOfAtOrAfterEndOfStringIsFalse()
    {
        $this->assertFalse($this->mutable->indexOf('baz', 11));
    }

    /**
     * @expectedException PHPUnit_Framework_Error
     */
    public function testIndexOfOutOfBoundsIfPositionPastStringLength()
    {
        $this->mutable->indexOf('baz', 12);
    }
}
