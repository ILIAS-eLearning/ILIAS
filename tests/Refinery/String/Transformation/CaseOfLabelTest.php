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
 *********************************************************************/

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery ;
use ILIAS\Refinery\Transformation;
use PHPUnit\Framework\TestCase;

class CaseOfLabelTest extends TestCase
{
    private const LANGUAGE_KEY = "en";
    private const SENSELESS_LANGUAGE_KEY = "this_language_key_will_never_exist";
    private const TEST_STRING_1 = "I am a test string for the title capitalization and I hope that works even if it is complicated :)";
    private const TEST_STRING_2 = "I switch the computer on and go online";
    private const TEST_STRING_3 = "Now it is working";
    private const EXPECTED_RESULT_TEST_STRING_1 = "I Am a Test String for the Title Capitalization and I Hope that Works even if It Is Complicated :)";
    private const EXPECTED_RESULT_TEST_STRING_2 = "I Switch the Computer on and Go Online";
    private const EXPECTED_RESULT_TEST_STRING_3 = "Now It Is Working";

    private ?Transformation $case_of_label_if_possible;
    private ?Refinery $f;

    protected function setUp() : void
    {
        $dataFactory = new DataFactory();

        $language = $this->createMock(ilLanguage::class);

        $this->f = new Refinery($dataFactory, $language);
        $this->case_of_label_if_possible = $this->f->string()->caseOfLabel(self::LANGUAGE_KEY);
    }

    protected function tearDown() : void
    {
        $this->f = null;
        $this->case_of_label_if_possible = null;
    }

    public function testTransform1() : void
    {
        $str = $this->case_of_label_if_possible->transform(self::TEST_STRING_1);

        $this->assertEquals(self::EXPECTED_RESULT_TEST_STRING_1, $str);
    }

    public function testTransform2() : void
    {
        $str = $this->case_of_label_if_possible->transform(self::TEST_STRING_2);

        $this->assertEquals(self::EXPECTED_RESULT_TEST_STRING_2, $str);
    }


    public function testTransform3() : void
    {
        $str = $this->case_of_label_if_possible->transform(self::TEST_STRING_3);

        $this->assertEquals(self::EXPECTED_RESULT_TEST_STRING_3, $str);
    }

    public function testTransformFails() : void
    {
        $raised = false;
        try {
            $arr = [];
            $next_str = $this->case_of_label_if_possible->transform($arr);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $int = 1001;
            $next_str = $this->case_of_label_if_possible->transform($int);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $std_class = new stdClass();
            $next_str = $this->case_of_label_if_possible->transform($std_class);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
    }

    public function testInvoke() : void
    {
        $this->case_of_label_if_possible = $this->f->string()->caseOfLabel(self::LANGUAGE_KEY);

        $str = ($this->case_of_label_if_possible)(self::TEST_STRING_1);

        $this->assertEquals(self::EXPECTED_RESULT_TEST_STRING_1, $str);
    }

    public function testInvokeFails() : void
    {
        $this->case_of_label_if_possible = $this->f->string()->caseOfLabel(self::LANGUAGE_KEY);

        $raised = false;
        try {
            $arr = [];
            $next_str = ($this->case_of_label_if_possible)($arr);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $int = 1001;
            $next_str = ($this->case_of_label_if_possible)($int);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);

        $raised = false;
        try {
            $std_class = new stdClass();
            $next_str = ($this->case_of_label_if_possible)($std_class);
        } catch (InvalidArgumentException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
    }

    public function testApplyToWithValidValueReturnsAnOkResult() : void
    {
        $factory = new DataFactory();

        $valueObject = $factory->ok(self::TEST_STRING_1);

        $resultObject = $this->case_of_label_if_possible->applyTo($valueObject);

        $this->assertEquals(self::EXPECTED_RESULT_TEST_STRING_1, $resultObject->value());
        $this->assertFalse($resultObject->isError());
    }

    public function testApplyToWithInvalidValueWillLeadToErrorResult() : void
    {
        $factory = new DataFactory();

        $valueObject = $factory->ok(42);

        $resultObject = $this->case_of_label_if_possible->applyTo($valueObject);

        $this->assertTrue($resultObject->isError());
    }

    public function testUnknownLanguageKey() : void
    {
        $this->case_of_label_if_possible = $this->f->string()->caseOfLabel(self::SENSELESS_LANGUAGE_KEY);

        $raised = false;
        try {
            $str = $this->case_of_label_if_possible->transform(self::TEST_STRING_1);
        } catch (LogicException $e) {
            $raised = true;
        }
        $this->assertTrue($raised);
    }
}
