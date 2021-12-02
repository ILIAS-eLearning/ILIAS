<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\UI\Component\Input;

use PHPUnit\Framework\TestCase;
use ILIAS\UI\Implementation\Component\Input\DynamicInputsTemplateNameSource;

/**
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class DynamicInputsTemplateNameSourceTest extends TestCase
{
    public function test_new_name_generation() : void
    {
        $expected_parent_name = 'parent_input_name_xyz';
        $expected_index_placeholder = DynamicInputsTemplateNameSource::INDEX_PLACEHOLDER;

        $name_source = new DynamicInputsTemplateNameSource($expected_parent_name);

        $this->assertEquals(
            $expected_parent_name . "[$expected_index_placeholder][dynamic_input_0]",
            $name_source->getNewName()
        );

        $this->assertEquals(
            $expected_parent_name . "[$expected_index_placeholder][dynamic_input_1]",
            $name_source->getNewName()
        );
    }
}