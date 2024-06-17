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

use ILIAS\UI\Implementation\Component\Input\ViewControl as Control;
use ILIAS\UI\Implementation\Component\SignalGenerator;
use ILIAS\Data\Order;
use ILIAS\Data\Range;
use ILIAS\Data\Result;
use ILIAS\UI\Implementation\Component\Input\InputData;

require_once('ViewControlBaseTest.php');

class ViewControlGroupTest extends ViewControlBaseTest
{
    public function testViewControlGroupCreation(): void
    {
        $f = $this->buildVCFactory();
        $vc1 = $f->nullControl();
        $vc2 = $f->pagination();
        $group = $f->group([$vc1, $vc2]);

        $this->assertEquals([$vc1, $vc2], $group->getInputs());
    }

    public function testViewControlGroupGetContent(): void
    {
        $f = $this->buildVCFactory();
        $input = $this->createMock(InputData::class);
        $namesource = new DefNamesource();

        $group = $f->group(
            [
                $f->nullControl(),
                $f->pagination(),
                $f->sortation(
                    [
                    'a' => new Order('field1', 'ASC'),
                    'b' => new Order('field2', 'ASC')]
                )
            ]
        )
        ->withNameFrom($namesource)
        ->withInput($input);

        $this->assertInstanceOf(Result\Ok::class, $group->getContent());
        $this->assertCount(3, $group->getContent()->value());
        list($a, $b, $c) = $group->getContent()->value();
        $this->assertNull($a);
        $this->assertInstanceOf(Range::class, $b);
        $this->assertInstanceOf(Order::class, $c);
    }

    public function testViewControlGroupRendering(): void
    {
        $f = $this->buildVCFactory();
        $group = $f->group(
            [
                $f->nullControl(),
                $f->pagination()
                    ->withLimitOptions([2, 5, 10])
                    ->withTotalCount(12)
                    ->withOnChange((new SignalGenerator())->create()),
                $f->sortation([
                    'a' => new Order('field1', 'ASC'),
                    'b' => new Order('field2', 'ASC')
                ])
                    ->withOnChange((new SignalGenerator())->create()),
                $f->fieldSelection(['A', 'B'])
                    ->withOnChange((new SignalGenerator())->create())
            ]
        );

        $html = $this->brutallyTrimHTML($this->getDefaultRenderer()->render($group));
        $this->assertStringContainsString('il-viewcontrol il-viewcontrol-pagination', $html);
        $this->assertStringContainsString('il-viewcontrol il-viewcontrol-sortation', $html);
        $this->assertStringContainsString('il-viewcontrol il-viewcontrol-fieldselection', $html);
    }
}
