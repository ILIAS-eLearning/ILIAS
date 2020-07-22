<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Transformation;

/**
 * TestCase for AddLabel transformations
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class AddLabelTest extends PHPUnit_Framework_TestCase
{
    protected static $labels = array("A", "B", "C");
    protected static $test_array = array(1, 2, 3);
    protected static $result_array = array("A" => 1, "B" => 2, "C" => 3);

    protected function setUp()
    {
        $this->f = new Transformation\Factory();
        $this->add_label = $this->f->addLabels(self::$labels);
    }

    protected function tearDown()
    {
        $this->f = null;
        $this->add_label = null;
    }

    public function testTransform()
    {
        $with = $this->add_label->transform(self::$test_array);
        $this->assertEquals(self::$result_array, $with);
    }

    public function testTransformFails()
    {
        $raised = false;
        try {
            $with = null;
            $next_with = $this->add_label->transform($with);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $without = array(1, 2, 3, 4);
            $with = $this->add_label->transform($without);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $without = "1, 2, 3";
            $with = $this->add_label->transform($without);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $std_class = new stdClass();
            $with = $this->add_label->transform($std_class);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
    }

    public function testInvoke()
    {
        $add_label = $this->f->addLabels(self::$labels);
        $with = $add_label(self::$test_array);
        $this->assertEquals(self::$result_array, $with);
    }

    public function testInvokeFails()
    {
        $add_label = $this->f->addLabels(self::$labels);

        $raised = false;
        try {
            $with = null;
            $next_with = $add_label($with);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $without = array(1, 2, 3, 4);
            $with = $add_label($without);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $without = "1, 2, 3";
            $with = $add_label($without);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $std_class = new stdClass();
            $with = $add_label($std_class);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
    }
}
