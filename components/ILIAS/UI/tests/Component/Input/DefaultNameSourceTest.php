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

use ILIAS\UI\Implementation\Component\Input\DefaultNameSource;
use PHPUnit\Framework\TestCase;

/**
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class DefaultNameSourceTest extends TestCase
{
    public function testGetNextName(): void
    {
        $name_source = new DefaultNameSource();

        $this->assertEquals('input_0', $name_source->getNextName());
        $this->assertEquals('input_1', $name_source->getNextName());
        $this->assertEquals('input_2', $name_source->getNextName());
    }

    public function testGetNextNameWithParentName(): void
    {
        $name_source = new DefaultNameSource();
        $parent_name = 'some_parent_name';
        $name_source = $name_source->withParentName($parent_name);

        $this->assertEquals("$parent_name/input_0", $name_source->getNextName());
        $this->assertEquals("$parent_name/input_1", $name_source->getNextName());
        $this->assertEquals("$parent_name/input_2", $name_source->getNextName());
    }

    public function testGetNextNameWithReset(): void
    {
        $name_source = new DefaultNameSource();
        $name_source->getNextName();
        $name_source->getNextName('some_dedicated_name');
        $name_source = $name_source->withParentName('some_parent_name');
        $name_source = $name_source->withReset();

        $this->assertEquals('input_0', $name_source->getNextName());
    }

    public function testGetNextNameWithDedicatedName(): void
    {
        $name_source = new DefaultNameSource();
        $dedicated_name = 'some_dedicated_name';

        $this->assertEquals($dedicated_name, $name_source->getNextName($dedicated_name));
        $this->expectException(\LogicException::class);
        $name_source->getNextName($dedicated_name);
    }

    public function testGetNextNameWithDedicatedNameAndWithParentName(): void
    {
        $name_source = new DefaultNameSource();
        $dedicated_name = 'some_dedicated_name';
        $parent_name = 'some_parent_name';

        $this->assertEquals($dedicated_name, $name_source->getNextName($dedicated_name));
        $name_source = $name_source->withParentName($parent_name);
        $this->assertEquals("$parent_name/$dedicated_name", $name_source->getNextName($dedicated_name));
        $this->expectException(\LogicException::class);
        $name_source->getNextName($dedicated_name);
    }

    public function testWithParentName(): void
    {
        $name_source_1 = new DefaultNameSource();
        $name_source_2 = $name_source_1->withParentName('some_parent_name');
        $this->assertNotSame($name_source_1, $name_source_2);
    }

    public function testWithReset(): void
    {
        $name_source_1 = new DefaultNameSource();
        $name_source_2 = $name_source_1->withReset();
        $this->assertNotSame($name_source_1, $name_source_2);
    }
}
