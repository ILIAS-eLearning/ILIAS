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

class VCPaginationTest extends VCBaseTest
{
    public function testViewControlPaginationConstruct(): void
    {
        $vc = $this->buildVCFactory()->pagination();

        $this->assertInstanceOf(Signal::class, $vc->getInternalSignal());
        $this->assertIsArray($vc->getLimitOptions());
        $this->assertEquals($vc::DEFAULT_DROPDOWN_LABEL_OFFSET, $vc->getLabel());
        $this->assertEquals($vc::DEFAULT_DROPDOWN_LABEL_LIMIT, $vc->getLabelLimit());
        $this->assertFalse($vc->isDisabled());
    }

    public function testViewControlPaginationWithWrongValue(): void
    {
        $this->expectException(\Exception::class);
        $vc = $this->buildVCFactory()->pagination()
            ->withValue('notokvalue:-2');
    }

    public function testViewControlPaginationMutators(): void
    {
        $o = [1,2,3];
        $l = 'limitlabel';
        $vc = $this->buildVCFactory()->pagination();
        $this->assertEquals($o, $vc->withLimitOptions($o)->getLimitOptions($o));
        $this->assertEquals($l, $vc->withLabelLimit($l)->getLabelLimit($l));
    }

    public function testViewControlPaginationWithInput(): void
    {
        $v = '5:25';

        $input = $this->createMock(InputData::class);
        $input->expects($this->once())
            ->method("getOr")
            ->willReturn($v);

        $vc = $this->buildVCFactory()->pagination()
            ->withNameFrom($this->getNamesource())
            ->withInput($input);

        $df = $this->buildDataFactory();
        $this->assertEquals(
            $df->ok($df->range(0, 25)),
            $vc->getContent()
        );
        $this->assertEquals($v, $vc->getValue());
    }

    public function testViewControlPaginationRendering(): void
    {
        $r = $this->getDefaultRenderer();
        $vc = $this->buildVCFactory()->pagination()
            ->withLimitOptions([10, 20, 30])
            ->withValue('40:20');

        $expected = $this->brutallyTrimHTML('
<div class="il-viewcontrol il-viewcontrol-pagination l-bar__element" id="">
    <a tabindex="0" class="glyph" aria-label="back" id="id_4">
        <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
    </a>
    <div class="dropdown il-viewcontrol-pagination__pages">
        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-label="pagination offset" aria-haspopup="true" aria-expanded="false" aria-controls="_ctrl_offset">22-42<span class="caret"></span></button>
        <ul id="_ctrl_offset" class="dropdown-menu">
            <li><button class="btn btn-link" id="id_1">1-21</button></li>
            <li><button class="btn btn-link" id="id_2">22-42</button></li>
        </ul>
    </div>
    <a tabindex="0" class="glyph" aria-label="next" id="id_3">
        <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
    </a>

    <div class="dropdown il-viewcontrol-pagination__num-of-items">
        <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown" aria-label="pagination limit" aria-haspopup="true" aria-expanded="false" aria-controls="_ctrl_limit"><span class="caret"></span></button>
        <ul id="_ctrl_limit" class="dropdown-menu">
            <li><button class="btn btn-link" id="id_5">10</button></li>
            <li><button class="btn btn-link" id="id_6">20</button></li>
            <li><button class="btn btn-link" id="id_7">30</button></li>
        </ul>
    </div>

    <div class="il-viewcontrol-value hidden" role="none"><input type="hidden" name="" value="40:20" /></div>
</div>
');

        $html = $this->brutallyTrimHTML($r->render($vc));
        $this->assertEquals($expected, $html);
    }
}
