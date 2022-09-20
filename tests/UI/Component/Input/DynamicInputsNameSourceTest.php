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

use ILIAS\UI\Implementation\Component\Input\DynamicInputsNameSource;
use PHPUnit\Framework\TestCase;

/**
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class DynamicInputsNameSourceTest extends TestCase
{
    public function testNewNameGeneration(): void
    {
        $expected_parent_name = 'parent_input_name_xyz';

        $name_source = new DynamicInputsNameSource($expected_parent_name);

        $this->assertEquals(
            $expected_parent_name . "[form_input_0][]",
            $name_source->getNewName()
        );

        $this->assertEquals(
            $expected_parent_name . "[form_input_1][]",
            $name_source->getNewName()
        );
    }
}
