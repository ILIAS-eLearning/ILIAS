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

namespace ILIAS\Tests\UI\Component\Input;

use ILIAS\UI\Implementation\Component\Input\ArrayInputData;
use PHPUnit\Framework\TestCase;
use LogicException;

/**
 * @author  Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ArrayInputDataTest extends TestCase
{
    public function testInvalidKeyWithoutDefault(): void
    {
        $test_key = 'test_key_1';
        $input_data = new ArrayInputData([]);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("'$test_key' is not contained in provided data.");
        $input_data->get($test_key);
    }

    public function testInvalidKeyWithDefault(): void
    {
        $test_key = 'test_key_1';
        $expected_value = 'expected_value_1';
        $input_data = new ArrayInputData([]);

        $this->assertEquals(
            $expected_value,
            $input_data->getOr($test_key, $expected_value)
        );
    }

    public function testValidKeyWithoutDefault(): void
    {
        $test_key = 'test_key_1';
        $expected_value = 'expected_value_1';
        $input_data = new ArrayInputData([
            $test_key => $expected_value,
        ]);

        $this->assertEquals(
            $expected_value,
            $input_data->get($test_key)
        );
    }

    public function testValidKeyWithDefault(): void
    {
        $test_key = 'test_key_1';
        $expected_value = 'expected_value_1';
        $input_data = new ArrayInputData([
            $test_key => $expected_value,
        ]);

        $this->assertNotNull($input_data->getOr($test_key, null));
        $this->assertEquals(
            $expected_value,
            $input_data->getOr($test_key, null)
        );
    }

    public function testDefaultValues(): void
    {
        $input_data = new ArrayInputData([]);

        $test_array = ['key1' => 'val1'];
        $test_integer = 999;
        $test_boolean = false;
        $test_string = 'test_string_1';
        $test_double = 1.2;

        $this->assertFalse($input_data->getOr('', $test_boolean));
        $this->assertEquals($test_integer, $input_data->getOr('', $test_integer));
        $this->assertEquals($test_array, $input_data->getOr('', $test_array));
        $this->assertEquals($test_string, $input_data->getOr('', $test_string));
        $this->assertEquals($test_double, $input_data->getOr('', $test_double));
    }
}
