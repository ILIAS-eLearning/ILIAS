<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

use ILIAS\UI\Implementation\Component\Input\DynamicInputsNameSource;
use PHPUnit\Framework\TestCase;

/**
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class DynamicInputsNameSourceTest extends TestCase
{
    public function test_new_name_generation() : void
    {
        $expected_parent_name = 'parent_input_name_xyz';

        $name_source = new DynamicInputsNameSource($expected_parent_name);

        $this->assertEquals(
            $expected_parent_name . "[form_input_0][]",
            $name_source->getNewName()
        );

        $this->assertEquals(
            $expected_parent_name . "[form_input_2][]",
            $name_source->getNewName()
        );
    }
}