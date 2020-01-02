<?php

/* Copyright (c) 2017 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Transformation;

/**
 * TestCase for Custom transformations
 *
 * @author Stefan Hecken <stefan.hecken@concepts-and-training.de>
 */
class TransformationsCustomTest extends PHPUnit_Framework_TestCase
{
    const TEST_STRING = "Test";

    protected function setUp()
    {
        $this->f = new Transformation\Factory();
        $this->custom = $this->f->custom(
            function ($value) {
                if (!is_string($value)) {
                    throw new InvalidArgumentException("'" . gettype($value) . "' is not a string.");
                }
                return $value;
            }
        );
    }

    protected function tearDown()
    {
        $this->f = null;
        $this->custom = null;
    }

    public function testTransform()
    {
        $result = $this->custom->transform(self::TEST_STRING);
        $this->assertEquals(self::TEST_STRING, $result);
    }

    public function testTransformFails()
    {
        $raised = false;
        try {
            $lower_string = $this->custom->transform(array());
        } catch (InvalidArgumentException $e) {
            $this->assertEquals("'array' is not a string.", $e->getMessage());
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $lower_string = $this->custom->transform(12345);
        } catch (InvalidArgumentException $e) {
            $this->assertEquals("'integer' is not a string.", $e->getMessage());
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $std_class = new stdClass();
            $lower_string = $this->custom->transform($std_class);
        } catch (InvalidArgumentException $e) {
            $this->assertEquals("'object' is not a string.", $e->getMessage());
            $raised = true;
        }
        $this->assertTrue($raised);
    }

    public function testInvoke()
    {
        $custom = $this->f->custom(
            function ($value) {
                if (!is_string($value)) {
                    throw new InvalidArgumentException("'" . gettype($value) . "' is not a string.");
                }
                return $value;
            }
        );

        $result = $custom(self::TEST_STRING);
        $this->assertEquals(self::TEST_STRING, $result);
    }

    public function testInvokeFails()
    {
        $custom = $this->f->custom(
            function ($value) {
                if (!is_string($value)) {
                    throw new InvalidArgumentException("'" . gettype($value) . "' is not a string.");
                }
                return $value;
            }
        );

        $raised = false;
        try {
            $lower_string = $custom(array());
        } catch (InvalidArgumentException $e) {
            $this->assertEquals("'array' is not a string.", $e->getMessage());
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $lower_string = $custom(12345);
        } catch (InvalidArgumentException $e) {
            $this->assertEquals("'integer' is not a string.", $e->getMessage());
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $std_class = new stdClass();
            $lower_string = $custom($std_class);
        } catch (InvalidArgumentException $e) {
            $this->assertEquals("'object' is not a string.", $e->getMessage());
            $raised = true;
        }
        $this->assertTrue($raised);
    }
}
