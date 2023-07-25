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
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\Data;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Signal;

require_once('ViewControlBaseTest.php');

class ViewControlFieldSelectionTest extends ViewControlBaseTest
{
    public function testViewControlFieldSelectionConstruct(): void
    {
        $options = [
            'opt1' => 'A',
            'opt2' => 'B'
        ];
        $vc = $this->buildVCFactory()->fieldSelection($options);
        $this->assertInstanceOf(Signal::class, $vc->getInternalSignal());
        $this->assertEquals($options, $vc->getOptions());
        $this->assertEquals('', $vc->getButtonLabel());
    }

    public function testViewControlFieldSelectionWithWrongValue(): void
    {
        $this->expectException(\Exception::class);
        $options = [
            'opt1' => 'A',
            'opt2' => 'B',
            'opt3' => 'C',
        ];
        $vc = $this->buildVCFactory()->fieldSelection($options)
            ->withValue('notokvalue,something');
    }

    public function testViewControlFieldSelectionWithInput(): void
    {
        $options = [
            'opt1' => 'A',
            'opt2' => 'B',
            'opt3' => 'C',
        ];
        $v = ['opt1','opt2'];

        $input = $this->createMock(InputData::class);
        $input->expects($this->once())
            ->method("getOr")
            ->willReturn($v);

        $vc = $this->buildVCFactory()->fieldSelection($options)
            ->withNameFrom($this->getNamesource())
            ->withInput($input);

        $df = $this->buildDataFactory();
        $this->assertEquals(
            $df->ok(['opt1','opt2']),
            $vc->getContent()
        );
        $this->assertEquals($v, $vc->getValue());
    }

    public function testViewControlFieldSelectionRendering(): void
    {
        $r = $this->getDefaultRenderer();
        $options = [
            'opt1' => 'A',
            'opt2' => 'B'
        ];
        $vc = $this->buildVCFactory()->fieldSelection($options)
            ->withOnChange((new SignalGenerator())->create());

        $expected = $this->brutallyTrimHTML('
<div class="dropdown il-viewcontrol il-viewcontrol-fieldselection l-bar__element" id="id_3">
    <button class="btn btn-ctrl dropdown-toggle" type="button" data-toggle="dropdown" aria-label="label_fieldselection" aria-haspopup="true" aria-expanded="false" aria-controls="id_3_ctrl"><span class="caret"></span></button>
        <ul id="id_3_ctrl" class="dropdown-menu">
            <li><input type="checkbox" value="opt1" id="id_1" /><label for="id_1">A</label></li>
            <li><input type="checkbox" value="opt2" id="id_2" /><label for="id_2">B</label></li>

            <button class="btn btn-default" id="id_4">label_fieldselection_refresh</button>
        </ul>
    <div class="il-viewcontrol-value" role="none"></div>
</div>
');
        $html = $this->brutallyTrimHTML($r->render($vc));
        $this->assertEquals($expected, $html);
    }

    public function testViewControlFieldSelectionRenderingOutsideContainer(): void
    {
        $this->expectException(\LogicException::class);
        $this->buildVCFactory()->fieldSelection([])->getOnChangeSignal();
    }
}
