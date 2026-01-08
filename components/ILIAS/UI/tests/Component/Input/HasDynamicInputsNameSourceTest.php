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

use ILIAS\UI\Implementation\Component\Input\HasDynamicInputsNameSource;
use PHPUnit\Framework\TestCase;
use NameSourceStubs;
use ILIAS\UI\Implementation\Component\Input\NameSource;

/**
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class HasDynamicInputsNameSourceTest extends TestCase
{
    use NameSourceStubs;

    public function testGetNextNameWithoutIndices(): void
    {
        $prefix = 'input_';
        $new_name_source = $this->createCountingNameSourceStub($prefix);
        $expected_parent_name = 'parent_input_name_xyz';

        $name_source = new HasDynamicInputsNameSource($new_name_source);
        $name_source = $name_source->withParentName($expected_parent_name)->withIndices(false);

        $this->assertEquals(
            $expected_parent_name . "[{$prefix}0][]",
            $name_source->getNextName()
        );

        $this->assertEquals(
            $expected_parent_name . "[{$prefix}1][]",
            $name_source->getNextName()
        );
    }

    public function testGetNextNameWitIndices(): void
    {
        $prefix = 'input_';
        $new_name_source = $this->createCountingNameSourceStub($prefix);
        $expected_parent_name = 'parent_input_name_xyz';

        $name_source = new HasDynamicInputsNameSource($new_name_source);
        $name_source = $name_source->withParentName($expected_parent_name)->withIndices(true);

        $this->assertEquals(
            $expected_parent_name . "[{$prefix}0][0]",
            $name_source->getNextName()
        );

        $this->assertEquals(
            $expected_parent_name . "[{$prefix}1][0]",
            $name_source->getNextName()
        );
    }

    public function testGetNextNameWitIndicesAndWithResetDefaultNameSource(): void
    {
        $prefix = 'input_';
        $start_count_one = 0;
        $start_count_two = 0;
        $expected_parent_name = 'parent_input_name_xyz';

        $new_name_source_two = $this->createCountingNameSourceStub($prefix, $start_count_two);

        // do not use createCountingNameSourceStub(), since this will return itself first on withReset().
        $new_name_source_one = $this->createMock(NameSource::class);
        $new_name_source_one->method('getNextName')->willReturnCallback(static function () use ($prefix, &$start_count_one) {
            return $prefix . ($start_count_one++);
        });
        $new_name_source_one->method('withParentName')->willReturnSelf();
        $new_name_source_one->method('withReset')->willReturn($new_name_source_two);

        $name_source = new HasDynamicInputsNameSource($new_name_source_one);
        $name_source = $name_source->withParentName($expected_parent_name)->withIndices(true);

        $this->assertEquals(
            $expected_parent_name . "[{$prefix}0][0]",
            $name_source->getNextName()
        );
        $this->assertEquals(
            $expected_parent_name . "[{$prefix}1][0]",
            $name_source->getNextName()
        );

        $name_source = $name_source->withResetDefaultNameSource();

        $this->assertEquals(
            $expected_parent_name . "[{$prefix}0][1]",
            $name_source->getNextName()
        );
        $this->assertEquals(
            $expected_parent_name . "[{$prefix}1][1]",
            $name_source->getNextName()
        );
    }

    public function testWithResetOnDefaultNameSource(): void
    {
        $default_name_source = $this->createFixedNameSourceStub('');
        $default_name_source->expects($this->exactly(2))->method('withReset');
        $name_source = new HasDynamicInputsNameSource($default_name_source);
        $name_source = $name_source->withResetDefaultNameSource();
        $name_source = $name_source->withReset();
    }

    public function testWithParentNameIsMandatory(): void
    {
        $default_name_source = $this->createMock(NameSource::class);
        $name_source = new HasDynamicInputsNameSource($default_name_source);
        $this->expectException(\LogicException::class);
        $name_source->getNextName();
    }
}
