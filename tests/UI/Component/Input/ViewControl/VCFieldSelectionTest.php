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

require_once('VCBaseTest.php');

class VCFieldSelectionTest extends VCBaseTest
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
        $this->assertEquals($vc::DEFAULT_DROPDOWN_LABEL, $vc->getLabel());
        $this->assertEquals($vc::DEFAULT_BUTTON_LABEL, $vc->getButtonLabel());
        $this->assertFalse($vc->isDisabled());
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
            $v = 'opt1,opt2';

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
            $vc = $this->buildVCFactory()->fieldSelection($options);

            $expected = $this->brutallyTrimHTML('
<div class="dropdown il-viewcontrol il-viewcontrol-fieldselection" id="id_1">
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-label="field selection" aria-haspopup="true" aria-expanded="false" aria-controls="id_1_ctrl">
        <span><a class="glyph" aria-label="bulletlist"><span class="glyphicon glyphicon-bulletlist" aria-hidden="true"></span></a></span>
    </button>
    <div id="id_1_ctrl" class="dropdown-menu">
        <ul>
            <li><input type="checkbox" value="opt1" /><span>A</span></li>
            <li><input type="checkbox" value="opt2" /><span>B</span></li>
        </ul>
        <button class="btn btn-default" id="id_2">refresh</button>
    </div>
    <div class="il-viewcontrol-value"><input type="hidden" name="" value="" /></div>
</div>
');
            $html = $this->brutallyTrimHTML($r->render($vc));
            $this->assertEquals($expected, $html);
        }
}
