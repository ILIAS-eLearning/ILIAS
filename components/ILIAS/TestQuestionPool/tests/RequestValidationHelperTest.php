<?php

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

declare(strict_types=1);

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\TestQuestionPool\ilTestLegacyFormsHelper;

class RequestValidationHelperTest extends assBaseTestCase
{
    private ilTestLegacyFormsHelper $object;

    protected function setUp(): void
    {
        parent::setUp();

        $lng_mock = $this->getMockBuilder(ILIAS\Language\Language::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->setGlobalVariable('refinery', new Refinery(new DataFactory(), $lng_mock));
        $this->object = new ilTestLegacyFormsHelper();
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ilTestLegacyFormsHelper::class, $this->object);
    }

    public function test_checkPointsFromRequest_shouldAbort_whenEmptyData(): void
    {
        $data = [];
        $expected = 'msg_input_is_required';
        $actual = $this->object->checkPointsInput($data, true);
        $this->assertEquals($expected, $actual);
    }

    public function test_checkPointsFromRequest_shouldAbort_whenEmptyPoints(): void
    {
        $data = ['points' => []];
        $expected = 'msg_input_is_required';
        $actual = $this->object->checkPointsInput($data, true);
        $this->assertEquals($expected, $actual);
    }

    public function test_checkPointsFromRequest_shouldReturn_whenEmptyPoints(): void
    {
        $data = ['points' => []];
        $expected = [];
        $actual = $this->object->checkPointsInput($data, false);
        $this->assertEquals($expected, $actual);
    }

    public function test_checkPointsFromRequest_shouldAbort_whenNonNumericPoints(): void
    {
        $data = ['points' => [1, 5.2, 'not a number']];
        $expected = 'form_msg_numeric_value_required';
        $actual = $this->object->checkPointsInput($data, false);
        $this->assertEquals($expected, $actual);
    }

    public function test_checkPointsFromRequest_shouldReturn_whenPointsAsString(): void
    {
        $data = ['points' => [1, 5.2, '7.8', '9,1']];
        $expected = [1.0, 5.2, 7.8, 9.1];
        $actual = $this->object->checkPointsInput($data, false);
        $this->assertEquals($expected, $actual);
    }

    public function test_checkPointsFromRequest_shouldAbort_whenZeroPoints(): void
    {
        $data = ['points' => [0, -6]];
        $expected = 'enter_enough_positive_points';
        $actual = $this->object->checkPointsInputEnoughPositive($data, true);
        $this->assertEquals($expected, $actual);
    }

    public function test_checkPointsFromRequest_shouldReturn_whenNonZeroPoints(): void
    {
        $data = ['points' => [1, 5.2, 7.8, 9.1]];
        $expected = [1.0, 5.2, 7.8, 9.1];
        $actual = $this->object->checkPointsInputEnoughPositive($data, true);
        $this->assertEquals($expected, $actual);
    }

    public function test_inArray_shouldReturnFalse_whenEmptyArray(): void
    {
        $array = [];
        $key = 'test';
        $this->assertFalse($this->object->inArray($array, $key));
    }

    public function test_inArray_shouldReturnFalse_whenKeyNotFound(): void
    {
        $array = ['not_test' => 'value'];
        $key = 'test';
        $this->assertFalse($this->object->inArray($array, $key));
    }

    public function test_inArray_shouldReturnTrue_whenKeyFound(): void
    {
        $array = ['test' => 'value'];
        $key = 'test';
        $this->assertTrue($this->object->inArray($array, $key));
    }
    public function test_inArray_shouldReturnFalse_whenEmptyStringValue(): void
    {
        $array = ['test' => ''];
        $key = 'test';
        $this->assertFalse($this->object->inArray($array, $key));
    }

    public function test_inArray_shouldReturnFalse_whenEmptyArrayValue(): void
    {
        $array = ['test' => []];
        $key = 'test';
        $this->assertFalse($this->object->inArray($array, $key));
    }
}
