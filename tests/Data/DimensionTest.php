<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 ********************************************************************
 */

require_once('./libs/composer/vendor/autoload.php');

use ILIAS\Data\Dimension;
use PHPUnit\Framework\TestCase;

class DimensionTest extends TestCase
{
    protected function setUp() : void
    {
        $this->f = new Dimension\Factory();
    }

    public function testCardinaltLabels() : void
    {
        $labels = ["label1", "label2", "label3"];
        $c = $this->f->cardinal($labels);
        $this->assertEquals($labels, $c->getLabels());
    }

    public function testRangeLabels() : void
    {
        $labels = ["label1", "label2", "label3"];
        $c = $this->f->cardinal($labels);
        $r = $this->f->range($c);
        $this->assertEquals($labels, $r->getLabels());
        $this->assertEquals($c->getLabels(), $r->getLabels());
    }

    public function testCardinalNumericValues() : void
    {
        try {
            $c = $this->f->cardinal();
            $c->checkValue(-2);
            $c->checkValue(0);
            $c->checkValue(0.5);
            $c->checkValue("1.5");
            $this->assertTrue(true);
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen.");
        }
    }

    public function testCardinalNullValue() : void
    {
        try {
            $c = $this->f->cardinal();
            $c->checkValue(null);
            $this->assertTrue(true);
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen.");
        }
    }

    public function testCardinalInvalidValue() : void
    {
        try {
            $c = $this->f->cardinal();
            $c->checkValue("A value");
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testRangeNullValue() : void
    {
        try {
            $c = $this->f->cardinal();
            $r = $this->f->range($c);
            $r->checkValue(null);
            $this->assertTrue(true);
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen.");
        }
    }

    public function testRangeNumericValues() : void
    {
        try {
            $c = $this->f->cardinal();
            $r = $this->f->range($c);
            $r->checkValue([-2, 0]);
            $r->checkValue([0.5, "1.5"]);
            $this->assertTrue(true);
        } catch (\InvalidArgumentException $e) {
            $this->assertFalse("This should not happen.");
        }
    }

    public function testRangeInvalidArray() : void
    {
        try {
            $c = $this->f->cardinal();
            $r = $this->f->range($c);
            $r->checkValue(2);
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testRangeInvalidCount() : void
    {
        try {
            $c = $this->f->cardinal();
            $r = $this->f->range($c);
            $r->checkValue([2]);
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }

    public function testRangeInvalidValues() : void
    {
        try {
            $c = $this->f->cardinal();
            $r = $this->f->range($c);
            $r->checkValue([2, "A value"]);
            $this->assertFalse("This should not happen.");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }
}
