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

namespace ILIAS\Tests\UI\Component\Input;

use ILIAS\UI\Implementation\Component\Input\StackedInputData;
use ILIAS\UI\Implementation\Component\Input\ArrayInputData;
use PHPUnit\Framework\TestCase;
use LogicException;

/**
 * @author  Richard Klees <richard.klees@concepts-and-training.de>
 */
class StackedInputDataTest extends TestCase
{
    protected StackedInputData $input;

    public function setUp(): void
    {
        $input_a = new ArrayInputData([ "in_a" => "a", "in_both" => "a"]);
        $input_b = new ArrayInputData([ "in_b" => "b", "in_both" => "b"]);
        $this->input = new StackedInputData($input_a, $input_b);
    }

    public function testInvalidKeyWithoutDefault(): void
    {
        $test_key = 'test_key_1';
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("'$test_key' is not contained in stack of input.");
        $this->input->get($test_key);
    }

    public function testInvalidKeyWithDefault(): void
    {
        $test_key = 'test_key_1';
        $expected_value = 'expected_value_1';
        $this->assertEquals(
            $expected_value,
            $this->input->getOr($test_key, $expected_value)
        );
    }

    public function testInFirstOnly(): void
    {
        $this->assertEquals("a", $this->input->get("in_a"));
    }

    public function testInFirstOnlyWithDefault(): void
    {
        $this->assertEquals("a", $this->input->getOr("in_a", "default"));
    }

    public function testInSecondOnly(): void
    {
        $this->assertEquals("b", $this->input->get("in_b"));
    }

    public function testInSecondOnlyWithDefault(): void
    {
        $this->assertEquals("b", $this->input->getOr("in_b", "default"));
    }

    public function testInBothOnly(): void
    {
        $this->assertEquals("a", $this->input->get("in_both"));
    }

    public function testInBothOnlyWithDefault(): void
    {
        $this->assertEquals("a", $this->input->getOr("in_both", "default"));
    }
}
