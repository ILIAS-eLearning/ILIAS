<?php
/* Copyright (c) 2017 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

require_once("libs/composer/vendor/autoload.php");

use ILIAS\Data;

/**
 * Tests working with color data object
 *
 * @author Nils Haagen <nils.haagen@concepts-and-training.de>
 */
class ColorTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->f = new Data\Factory();
    }

    protected function tearDown()
    {
        $this->f = null;
    }

    public function testFullHexValue()
    {
        $v = $this->f->color('#0fff2f');

        $this->assertEquals('#0fff2f', $v->asHex());
        $this->assertEquals('rgb(15, 255, 47)', $v->asRGBString());
        $this->assertEquals(array(15, 255, 47), $v->asArray());
        $this->assertEquals(15, $v->r());
        $this->assertEquals(255, $v->g());
        $this->assertEquals(47, $v->b());
    }

    public function testShortHexValue()
    {
        $v = $this->f->color('#f0f');
        $this->assertEquals('#ff00ff', $v->asHex());
        $this->assertEquals('rgb(255, 0, 255)', $v->asRGBString());
        $this->assertEquals(array(255, 0, 255), $v->asArray());
    }

    public function testShortHexValue2()
    {
        $v = $this->f->color('f0f');
        $this->assertEquals('#ff00ff', $v->asHex());
        $this->assertEquals('rgb(255, 0, 255)', $v->asRGBString());
        $this->assertEquals(array(255, 0, 255), $v->asArray());
    }

    public function testRBGValue()
    {
        $v = $this->f->color(array(15,255,47));
        $this->assertEquals('#0fff2f', $v->asHex());
        $this->assertEquals('rgb(15, 255, 47)', $v->asRGBString());
        $this->assertEquals(array(15, 255, 47), $v->asArray());
    }

    public function testWrongRBGValue()
    {
        try {
            $v = $this->f->color(array(-1,0,0));
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testWrongRBGValue2()
    {
        try {
            $v = $this->f->color(array(256,0,0));
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testWrongRBGValue3()
    {
        try {
            $v = $this->f->color(array(1,1,'123'));
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testWrongRBGValue4()
    {
        try {
            $v = $this->f->color(array());
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testWrongHexValue()
    {
        try {
            $v = $this->f->color('1234');
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testWrongHexValue2()
    {
        try {
            $v = $this->f->color('#ff');
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testWrongHexValue4()
    {
        try {
            $v = $this->f->color('#gg0000');
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testDarkness()
    {
        $v = $this->f->color('#6541f4');
        $this->assertEquals(true, $v->isDark());
    }

    public function testDarkness2()
    {
        $v = $this->f->color('#c1f441');
        $this->assertEquals(false, $v->isDark());
    }
}
