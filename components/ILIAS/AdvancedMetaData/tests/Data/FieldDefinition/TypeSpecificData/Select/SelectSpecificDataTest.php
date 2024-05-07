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

namespace ILIAS\AdvancedMetaData\Data\FieldDefinition\TypeSpecificData\Select;

use PHPUnit\Framework\TestCase;

class SelectSpecificDataTest extends TestCase
{
    public function testGetOptionsSortedByPosition(): void
    {
        $option_1 = new OptionImplementation(5, 13);
        $option_2 = new OptionImplementation(0, 13);
        $option_3 = new OptionImplementation(32, 13);
        $data = new SelectSpecificDataImplementation(1, $option_1, $option_2, $option_3);

        $options = $data->getOptions();
        $this->assertSame(
            $option_2,
            $options->current()
        );
        $options->next();
        $this->assertSame(
            $option_1,
            $options->current()
        );
        $options->next();
        $this->assertSame(
            $option_3,
            $options->current()
        );
    }

    public function testHasOptionsTrue(): void
    {
        $option = new OptionImplementation(5, 13);
        $data = new SelectSpecificDataImplementation(1, $option);
        $this->assertTrue($data->hasOptions());
    }

    public function testHasOptionsFalse(): void
    {
        $data = new SelectSpecificDataImplementation(1);
        $this->assertFalse($data->hasOptions());
    }

    public function testGetOption(): void
    {
        $option = new OptionImplementation(5, 13);
        $data = new SelectSpecificDataImplementation(1, $option);
        $this->assertSame(
            $option,
            $data->getOption(13)
        );
    }

    public function testAddOption(): void
    {
        $data = new SelectSpecificDataImplementation(1);
        $option = $data->addOption();
        $this->assertSame(
            $option,
            $data->getOptions()->current()
        );
    }

    public function testRemoveOption(): void
    {
        $option = new OptionImplementation(5, 13);
        $data = new SelectSpecificDataImplementation(1, $option);
        $data->removeOption(13);
        $this->assertNull($data->getOption(13));
    }

    public function testContainsChangesOptionRemoved(): void
    {
        $option = new OptionImplementation(5, 13);
        $data = new SelectSpecificDataImplementation(1, $option);
        $data->removeOption(13);
        $this->assertTrue($data->containsChanges());
    }
}
