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

use ILIAS\Refinery\Transformation;
use ILIAS\Data\Factory as DataFactory;
use PHPUnit\Framework\TestCase;
use ILIAS\Refinery\Factory as Refinery;

class SplitStringTest extends TestCase
{
    private const STRING_TO_SPLIT = "I am#a test string#for split";

    /** @var string[] */
    protected static array $result = ["I am", "a test string", "for split"];

    private ?Transformation $split_string;
    private ?Refinery $f;

    protected function setUp(): void
    {
        $dataFactory = new DataFactory();
        $language = $this->createMock(ilLanguage::class);
        $this->f = new Refinery($dataFactory, $language);
        $this->split_string = $this->f->string()->splitString("#");
    }

    protected function tearDown(): void
    {
        $this->f = null;
        $this->split_string = null;
    }

    public function testTransform(): void
    {
        $arr = $this->split_string->transform(self::STRING_TO_SPLIT);
        $this->assertEquals(static::$result, $arr);
    }

    public function testTransformFails(): void
    {
        $raised = false;
        try {
            $arr = [];
            $next_arr = $this->split_string->transform($arr);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $without = 1001;
            $with = $this->split_string->transform($without);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $std_class = new stdClass();
            $with = $this->split_string->transform($std_class);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
    }

    public function testInvoke(): void
    {
        $split_string = $this->f->string()->splitString("#");
        $arr = $split_string(self::STRING_TO_SPLIT);
        $this->assertEquals(static::$result, $arr);
    }

    public function testInvokeFails(): void
    {
        $split_string = $this->f->string()->splitString("#");

        $raised = false;
        try {
            $arr = [];
            $next_arr = $split_string($arr);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $number = 1001;
            $with = $split_string($number);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $std_class = new stdClass();
            $with = $split_string($std_class);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
    }

    public function testApplyToWithValidValueReturnsAnOkResult(): void
    {
        $factory = new DataFactory();
        $valueObject = $factory->ok(self::STRING_TO_SPLIT);

        $resultObject = $this->split_string->applyTo($valueObject);

        $this->assertEquals(self::$result, $resultObject->value());
        $this->assertFalse($resultObject->isError());
    }

    public function testApplyToWithInvalidValueWillLeadToErrorResult(): void
    {
        $factory = new DataFactory();
        $valueObject = $factory->ok(42);

        $resultObject = $this->split_string->applyTo($valueObject);

        $this->assertTrue($resultObject->isError());
    }
}
