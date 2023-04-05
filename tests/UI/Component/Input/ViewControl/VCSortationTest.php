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

class VCSortationTest extends VCBaseTest
{
    public function testViewControlSortationConstruct(): void
    {
        $options = [
            'opt:ASC' => 'A',
            'opt:DESC' => 'B'
        ];
        $vc = $this->buildVCFactory()->sortation($options);

        $this->assertInstanceOf(Signal::class, $vc->getInternalSignal());
        $this->assertEquals($options, $vc->getOptions());
        $this->assertEquals($vc::DEFAULT_DROPDOWN_LABEL, $vc->getLabel());
        $this->assertFalse($vc->isDisabled());
    }

    public function testViewControlSortationWithWrongValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $options = [
            'opt:ASC' => 'A',
            'opt:DESC' => 'B'
        ];
        $vc = $this->buildVCFactory()->sortation($options)
            ->withValue('notokvalue:DESC');
    }

    public function testViewControlSortationWithInput(): void
    {
        $options = [
            'opt:ASC' => 'A',
            'opt:DESC' => 'B'
        ];
        $v = 'opt:DESC';

        $input = $this->createMock(InputData::class);
        $input->expects($this->once())
            ->method("getOr")
            ->willReturn($v);

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
            'opt:ASC' => 'A',
            'opt:DESC' => 'B'
        ];
        $vc = $this->buildVCFactory()->sortation($options);

        $expected = $this->brutallyTrimHTML('
<div class="dropdown il-viewcontrol il-viewcontrol-sortation l-bar__element" id="">
    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-label="sortation" aria-haspopup="true" aria-expanded="false" aria-controls="_ctrl"><span class="caret"></span></button>
    <ul id="_ctrl" class="dropdown-menu">
        <li><button class="btn btn-link" id="id_1">A</button></li>
        <li><button class="btn btn-link" id="id_2">B</button></li>
    </ul>
    <div class="il-viewcontrol-value" role="none"><input type="hidden" name="" value="" /></div>
</div>
');
        $html = $this->brutallyTrimHTML($r->render($vc));
        $this->assertEquals($expected, $html);
    }
}
