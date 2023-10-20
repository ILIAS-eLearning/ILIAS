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

use ILIAS\UI\Implementation\Component\Input\FormInputNameSource;
use PHPUnit\Framework\TestCase;

/**
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class FormInputNameSourceTest extends TestCase
{
    public function testNewNameGeneration(): void
    {
        $name_source = new FormInputNameSource();

        $this->assertEquals(
            'input_0',
            $name_source->getNewName()
        );

        $this->assertEquals(
            'input_1',
            $name_source->getNewName()
        );

        $this->assertEquals(
            'dedicated',
            $name_source->getNewDedicatedName('dedicated')
        );

        $this->assertEquals(
            'input_2',
            $name_source->getNewName()
        );

        $this->assertEquals(
            'dedicated_3',
            $name_source->getNewDedicatedName('dedicated')
        );
    }
}
