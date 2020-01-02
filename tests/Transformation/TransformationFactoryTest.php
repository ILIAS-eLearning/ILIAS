<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Transformation;

/**
 * TestCase for the factory of transformations
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class TransformationFactoryTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->f = new Transformation\Factory();
    }

    protected function tearDown()
    {
        $this->f = null;
    }

    public function testAddLabels()
    {
        $add_label = $this->f->addLabels(array("A", "B", "C"));
        $this->assertInstanceOf(Transformation\Transformation::class, $add_label);
    }

    public function testSplitString()
    {
        $split_string = $this->f->splitString("#");
        $this->assertInstanceOf(Transformation\Transformation::class, $split_string);
    }

    public function testCustom()
    {
        $custom = $this->f->custom(function () {
        });
        $this->assertInstanceOf(Transformation\Transformation::class, $custom);
    }

    public function testToData()
    {
        $data = $this->f->toData('password');
        $this->assertInstanceOf(Transformation\Transformation::class, $data);
    }

    public function testToDataWrongType()
    {
        try {
            $data = $this->f->toData('no_such_type');
            $this->assertFalse("This should not happen");
        } catch (\InvalidArgumentException $e) {
            $this->assertTrue(true);
        }
    }
}
