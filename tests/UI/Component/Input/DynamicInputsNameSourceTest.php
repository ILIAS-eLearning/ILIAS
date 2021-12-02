<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\UI\Component\Input\Field;

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Component\Input\DynamicInputsNameSource;

/**
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class DynamicInputsNameSourceTest extends TestCase
{
    public function test_new_name_generation() : void
    {
        $expected_parent_name = 'parent_input_name_xyz';
        $expected_absolute_index = 100;

        $name_source = new DynamicInputsNameSource($expected_parent_name, $expected_absolute_index);

        $this->assertEquals(
            $expected_parent_name . "[$expected_absolute_index][dynamic_input_1]",
            $name_source->getNewName()
        );

        $this->assertEquals(
            $expected_parent_name . "[$expected_absolute_index][dynamic_input_2]",
            $name_source->getNewName()
        );
    }
}