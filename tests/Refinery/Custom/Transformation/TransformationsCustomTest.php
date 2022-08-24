<?php

declare(strict_types=1);

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
 *********************************************************************/

use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Transformation;
use PHPUnit\Framework\TestCase;

class TransformationsCustomTest extends TestCase
{
    private const TEST_STRING = "Test";

    private ?Transformation $custom;
    private ?Refinery $f;

    protected function setUp(): void
    {
        $language = $this->createMock(ilLanguage::class);
        $this->f = new Refinery(new DataFactory(), $language);

        $this->custom = $this->f->custom()->transformation(
            function ($value) {
                if (!is_string($value)) {
                    throw new InvalidArgumentException("'" . gettype($value) . "' is not a string.");
                }
                return $value;
            }
        );
    }

    protected function tearDown(): void
    {
        $this->f = null;
        $this->custom = null;
    }

    public function testTransform(): void
    {
        $result = $this->custom->transform(self::TEST_STRING);
        $this->assertEquals(self::TEST_STRING, $result);
    }

    public function testTransformFails(): void
    {
        $raised = false;
        try {
            $lower_string = $this->custom->transform([]);
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

    public function testInvoke(): void
    {
        $custom = $this->f->custom()->transformation(
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

    public function testInvokeFails(): void
    {
        $custom = $this->f->custom()->transformation(
            function ($value) {
                if (!is_string($value)) {
                    throw new InvalidArgumentException("'" . gettype($value) . "' is not a string.");
                }
                return $value;
            }
        );

        $raised = false;
        try {
            $lower_string = $custom([]);
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

    public function testApplyToWithValidValueReturnsAnOkResult(): void
    {
        $factory = new DataFactory();
        $valueObject = $factory->ok(self::TEST_STRING);

        $resultObject = $this->custom->applyTo($valueObject);

        $this->assertEquals(self::TEST_STRING, $resultObject->value());
        $this->assertFalse($resultObject->isError());
    }
}
