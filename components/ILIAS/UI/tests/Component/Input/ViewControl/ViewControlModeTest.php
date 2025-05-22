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
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Component\Input\InputData;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Signal;

require_once('ViewControlTestBase.php');

class ViewControlModeTest extends ViewControlTestBase
{
    public function testViewControlFieldSelectionConstruct(): void
    {
        $options = [
            'opt1' => 'A',
            'opt2' => 'B'
        ];
        $vc = $this->buildVCFactory()->mode($options);
        $this->assertEquals($options, $vc->getOptions());
    }

    public function testViewControlModeWithWrongValue(): void
    {
        $this->expectException(\Exception::class);
        $options = [
            'opt1' => 'A',
            'opt2' => 'B',
            'opt3' => 'C',
        ];
        $vc = $this->buildVCFactory()->mode($options)
            ->withValue(123);
    }

    public function testViewControlModeWithInput(): void
    {
        $options = [
            'opt1' => 'A',
            'opt2' => 'B',
            'opt3' => 'C',
        ];
        $v = 'opt2';

        $input = $this->createMock(InputData::class);
        $input->expects($this->once())
            ->method("getOr")
            ->willReturn($v);

        $vc = $this->buildVCFactory()->mode($options)
            ->withNameFrom($this->getNamesource())
            ->withInput($input);

        $df = $this->buildDataFactory();
        $this->assertEquals(
            $df->ok('opt2'),
            $vc->getContent()
        );
        $this->assertEquals($v, $vc->getValue());
    }

    public function testViewControlModeRendering(): void
    {
        $r = $this->getDefaultRenderer();
        $options = [
            'opt1' => 'A',
            'opt2' => 'B'
        ];
        $vc = $this->buildVCFactory()->mode($options)
            ->withOnChange((new SignalGenerator())->create());

        $expected = $this->brutallyTrimHTML('
            <div class="il-viewcontrol il-viewcontrol-mode l-bar__element" aria-label="label_modeviewcontrol" role="group">
                <button class="btn btn-default engaged" aria-pressed="true" data-action="#" id="id_1">A</button>
                <button class="btn btn-default" aria-pressed="false" data-action="#" id="id_2">B</button>
                <div class="il-viewcontrol-value" role="none">
                    <input type="hidden" name="" value="opt1" />
                </div>
            </div>
        ');
        $html = $this->brutallyTrimHTML($r->render($vc));
        $this->assertEquals($expected, $html);
    }

    public function testViewControlModeRenderingOutsideContainer(): void
    {
        $this->expectException(\LogicException::class);
        $this->buildVCFactory()->mode([])->getOnChangeSignal();
    }

    public function testViewControlModeWithoutOptions(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $vc = $this->buildVCFactory()->mode([]);
    }

    public function testViewControlModeWithWrongOptions(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $vc = $this->buildVCFactory()->mode([1,2,3]);
    }

}
