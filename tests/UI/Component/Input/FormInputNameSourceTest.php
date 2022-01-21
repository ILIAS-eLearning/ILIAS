<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\Tests\UI\Component\Input;

use ILIAS\UI\Implementation\Component\Input\FormInputNameSource;
use PHPUnit\Framework\TestCase;

/**
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class FormInputNameSourceTest extends TestCase
{
    public function testNewNameGeneration() : void
    {
        $name_source = new FormInputNameSource();

        $this->assertEquals(
            'form_input_0',
            $name_source->getNewName()
        );

        $this->assertEquals(
            'form_input_1',
            $name_source->getNewName()
        );
    }
}