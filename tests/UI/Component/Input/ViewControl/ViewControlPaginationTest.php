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


class VCPaginationRendererMock extends ILIAS\UI\Implementation\Component\Input\ViewControl\Renderer
{
    public function mock_buildRanges(int $total_count, int $page_limit)
    {
        return $this->buildRanges($total_count, $page_limit);
    }
    public function mock_findCurrentPage(array $ranges, int $offset)
    {
        return $this->findCurrentPage($ranges, $offset);
    }
    public function mocK_sliceRangesToVisibleEntries(array $ranges, int $current, int $number_of_visible_entries)
    {
        return $this->sliceRangesToVisibleEntries($ranges, $current, $number_of_visible_entries);
    }
}

class ViewControlPaginationTest extends ViewControlBaseTest
{
    public function testViewControlPaginationConstruct(): void
    {
        $vc = $this->buildVCFactory()->pagination();

        $this->assertInstanceOf(Signal::class, $vc->getInternalSignal());
        $this->assertIsArray($vc->getLimitOptions());
        $this->assertEquals('', $vc->getLabelLimit());
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
    }

    public static function providePaginationInput(): array
    {
        return [
            [
                'offset' => 24,
                'limit' => 25,
                'expected' => [0, 25]
            ],
            [
                'offset' => 25,
                'limit' => 25,
                'expected' => [25, 25]
            ],
            [
                'offset' => 52,
                'limit' => 25,
                'expected' => [50, 25]
            ],
            [
                'offset' => 7,
                'limit' => 5,
                'expected' => [5, 5]
            ],
            [
                'offset' => 99,
                'limit' => 5,
                'expected' => [95, 5]
            ],
            [
                'offset' => 4,
                'limit' => 3,
                'expected' => [3, 3]
            ],
            [
                'offset' => 4,
                'limit' => PHP_INT_MAX,
                'expected' => [0, PHP_INT_MAX]
            ],
            [
                'offset' => 0,
                'limit' => 2,
                'expected' => [0, 2]
            ],
            [
                'offset' => 10,
                'limit' => 0,
                'expected' => [10, 5] //default smallest limit
            ],

        ];
    }

    /**
     * @dataProvider providePaginationInput
     */
    public function testViewControlPaginationWithInput(
        int $offset,
        int $page_size,
        array $expected
    ): void {
        $v = [
            'offset' => $offset,
            'limit' => $page_size
        ];
        $input = $this->createMock(InputData::class);
        $input->expects($this->exactly(2))
            ->method("getOr")
            ->will(
                $this->onConsecutiveCalls($v["offset"], $v["limit"])
            );

        $vc = $this->buildVCFactory()->pagination()
            ->withNameFrom($this->getNamesource())
            ->withInput($input);

        $df = $this->buildDataFactory();
        $this->assertEquals(
            $df->ok($df->range(...$expected)),
            $vc->getContent()
        );

        $this->assertEquals(
            ['offset' => $offset, 'limit' => $page_size],
            $vc->getValue()
        );
    }

    public function testViewControlPaginationRendering(): void
    {
        $r = $this->getDefaultRenderer();
        $vc = $this->buildVCFactory()->pagination()
            ->withLimitOptions([2, 5, 10])
            ->withTotalCount(42)
            ->withValue(["offset" => 12, "limit" => 2])
            ->withOnChange((new SignalGenerator())->create());

        $expected = $this->brutallyTrimHTML('
<div class="il-viewcontrol il-viewcontrol-pagination l-bar__element" id="id_13">
    <div class="dropdown il-viewcontrol-pagination__sectioncontrol">
            <div class="btn btn-ctrl browse previous">
                <a tabindex="0" class="glyph" aria-label="back" id="id_8">
                    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
                </a>
            </div>

        <button class="btn btn-link" id="id_1">1</button>
        <span class="il-viewcontrol-pagination__spacer">...</span>
        <button class="btn btn-link" id="id_2">3</button>
        <button class="btn btn-link" id="id_3">4</button>
        <button class="btn btn-link engaged" aria-pressed="true" id="id_4">5</button>
        <button class="btn btn-link" id="id_5">6</button>
        <button class="btn btn-link" id="id_6">7</button>
        <span class="il-viewcontrol-pagination__spacer">...</span>
        <button class="btn btn-link" id="id_7">15</button>

        <div class="btn btn-ctrl browse next">
            <a tabindex="0" class="glyph" aria-label="next" id="id_9">
                <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
            </a>
        </div>
    </div>

    <div class="dropdown il-viewcontrol-pagination__num-of-items">
        <button class="btn btn-ctrl dropdown-toggle" type="button" data-toggle="dropdown" aria-label="label_pagination_limit" aria-haspopup="true" aria-expanded="false" aria-controls="id_13_ctrl_limit">
            <span class="caret"></span>
        </button>
        <ul id="id_13_ctrl_limit" class="dropdown-menu">
            <li class="selected"><button class="btn btn-link" id="id_10">2</button></li>
            <li><button class="btn btn-link" id="id_11">5</button></li>
            <li><button class="btn btn-link" id="id_12">10</button></li>
        </ul>
    </div>

    <div class="il-viewcontrol-value hidden" role="none">
        <input id="id_14" type="hidden" name="" value="12" />
        <input id="id_15" type="hidden" name="" value="2" />
    </div>
</div>
        ');

        $html = $this->brutallyTrimHTML($r->render($vc));
        $this->assertEquals($expected, $html);
    }

    protected function getStubRenderer()
    {
        return new VCPaginationRendererMock(
            $this->getUIFactory(),
            $this->getTemplateFactory(),
            $this->getLanguage(),
            $this->getJavaScriptBinding(),
            $this->getRefinery(),
            $this->getImagePathResolver(),
            $this->getDataFactory(),
            $this->getHelpTextRetriever(),
            $this->getUploadLimitResolver()
        );
    }

    public function testViewControlPaginationRenderingRanges(): void
    {
        $r = $this->getStubRenderer();
        $ranges = $r->mock_buildRanges(8, 3);
        $this->assertEquals(3, count($ranges));
        $ranges = $r->mock_buildRanges(10, 5);
        $this->assertEquals(2, count($ranges));
        $ranges = $r->mock_buildRanges(101, 5);
        $this->assertEquals(17, count($ranges));
    }

    public function testViewControlPaginationRenderingFindCurrent(): void
    {
        $r = $this->getStubRenderer();
        $ranges = $r->mock_buildRanges(20, 5);
        $this->assertEquals(0, $r->mock_findCurrentPage($ranges, 5));
        $this->assertEquals(1, $r->mock_findCurrentPage($ranges, 6));
        $this->assertEquals(3, $r->mock_findCurrentPage($ranges, 19));
    }

    public function testViewControlPaginationRenderingEntries(): void
    {
        $r = $this->getStubRenderer();
        $ranges = $r->mock_buildRanges(200, 5);
        $slices = $r->mock_sliceRangesToVisibleEntries($ranges, 6, 5);
        $this->assertEquals(5, count($slices));
        $this->assertEquals(0, $slices[0]->getStart());
        $this->assertEquals(30, $slices[1]->getStart());
        $this->assertEquals(36, $slices[2]->getStart());
        $this->assertEquals(42, $slices[3]->getStart());
        $this->assertEquals(198, $slices[4]->getStart());
    }

    public function testViewControlPaginationRenderingOutsideContainer(): void
    {
        $this->expectException(\LogicException::class);
        $this->buildVCFactory()->pagination()->getOnChangeSignal();
    }
}
