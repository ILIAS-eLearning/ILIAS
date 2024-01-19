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
use ILIAS\Data\Order;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Signal;

require_once('ViewControlBaseTest.php');

class ViewControlSortationTest extends ViewControlBaseTest
{
    public function testViewControlSortationConstruct(): void
    {
        $options = [
            'A' => new Order('opt', 'ASC'),
            'B' => new Order('opt', 'DESC')
        ];
        $vc = $this->buildVCFactory()->sortation($options);
        $r = ILIAS\UI\Implementation\Component\Input\ViewControl\Renderer::class;

        $this->assertInstanceOf(Signal::class, $vc->getInternalSignal());
        $this->assertEquals($options, $vc->getOptions());
    }

    public function testViewControlSortationWithWrongValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $options = [
            'A' => new Order('opt', 'ASC'),
            'B' => new Order('opt', 'DESC')
        ];
        $vc = $this->buildVCFactory()->sortation($options)
            ->withValue('notokvalue:DESC');
    }

    public function testViewControlSortationWithInput(): void
    {
        $options = [
            'A' => new Order('opt', 'ASC'),
            'B' => new Order('opt', 'DESC')
        ];
        $v = ['opt', 'DESC'];

        $input = $this->createMock(InputData::class);
        $input->expects($this->exactly(2))
            ->method("getOr")
            ->will(
                $this->onConsecutiveCalls($v[0], $v[1])
            );

        $vc = $this->buildVCFactory()->sortation($options)
            ->withNameFrom($this->getNamesource())
            ->withInput($input);

        $df = $this->buildDataFactory();
        $this->assertEquals(
            $df->ok($df->order('opt', 'DESC')),
            $vc->getContent()
        );
        $this->assertEquals($v, $vc->getValue());
    }

    public function testViewControlFieldSortationRendering(): void
    {
        $r = $this->getDefaultRenderer();
        $options = [
            'A' => new Order('opt', 'ASC'),
            'B' => new Order('opt', 'DESC')
        ];
        $vc = $this->buildVCFactory()->sortation($options)
            ->withOnChange((new SignalGenerator())->create());

        $expected = $this->brutallyTrimHTML('
<div class="dropdown il-viewcontrol il-viewcontrol-sortation l-bar__element" id="id_3">
    <button class="btn btn-ctrl dropdown-toggle" type="button" data-toggle="dropdown" aria-label="label_sortation" aria-haspopup="true" aria-expanded="false" aria-controls="id_3_ctrl"><span class="caret"></span></button>
    <ul id="id_3_ctrl" class="dropdown-menu">
        <li><button class="btn btn-link" id="id_1">A</button></li>
        <li><button class="btn btn-link" id="id_2">B</button></li>
    </ul>
    <div class="il-viewcontrol-value" role="none">
        <input id="id_4" type="hidden" value="" />
        <input id="id_5" type="hidden" value="" />
    </div>
</div>
');
        $html = $this->brutallyTrimHTML($r->render($vc));
        $this->assertEquals($expected, $html);
    }

    public function testViewControlSortationRenderingOutsideContainer(): void
    {
        $options = ['A' => new Order('opt', 'ASC')];
        $this->expectException(\LogicException::class);
        $this->buildVCFactory()->sortation($options)->getOnChangeSignal();
    }
}
