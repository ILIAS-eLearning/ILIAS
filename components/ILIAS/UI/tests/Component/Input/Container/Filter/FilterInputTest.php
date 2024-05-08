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

require_once(__DIR__ . "/../../../../../../../../vendor/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../../Base.php");
require_once(__DIR__ . "/FilterTest.php");

use ILIAS\Data;
use ILIAS\UI\Implementation\Component as I;

class FilterInputsTestNoUIFactories extends NoUIFactory
{
    protected I\Symbol\Factory $symbol_factory;
    protected I\Popover\Factory $popover_factory;
    protected I\Legacy\Factory $legacy_factory;

    public function __construct(
        I\Symbol\Factory $symbol_factory,
        I\Popover\Factory $popover_factory,
        I\Legacy\Factory $legacy_factory,
    ) {
        $this->symbol_factory = $symbol_factory;
        $this->popover_factory = $popover_factory;
        $this->legacy_factory = $legacy_factory;
    }

    public function symbol(): I\Symbol\Factory
    {
        return $this->symbol_factory;
    }

    public function popover(): I\Popover\Factory
    {
        return $this->popover_factory;
    }

    public function legacy($content): I\Legacy\Legacy
    {
        return $this->legacy_factory->legacy("");
    }
}

/**
 * Test on rendering filter inputs
 */

class FilterInputTest extends ILIAS_UI_TestBase
{
    protected function buildFactory(): I\Input\Container\Filter\Factory
    {
        return new I\Input\Container\Filter\Factory(
            new I\SignalGenerator(),
            $this->buildInputFactory()
        );
    }

    protected function buildInputFactory(): I\Input\Field\Factory
    {
        $df = new Data\Factory();
        $language = $this->createMock(ilLanguage::class);
        return new I\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new I\SignalGenerator(),
            $df,
            new ILIAS\Refinery\Factory($df, $language),
            $language
        );
    }

    protected function buildSymbolFactory(): I\Symbol\Factory
    {
        return new I\Symbol\Factory(
            new I\Symbol\Icon\Factory(),
            new I\Symbol\Glyph\Factory(),
            new I\Symbol\Avatar\Factory()
        );
    }

    protected function buildPopoverFactory(): I\Popover\Factory
    {
        return new I\Popover\Factory(new I\SignalGenerator());
    }

    protected function buildLegacyFactory(): I\Legacy\Factory
    {
        return new I\Legacy\Factory(new I\SignalGenerator());
    }

    public function getUIFactory(): FilterInputsTestNoUIFactories
    {
        return new FilterInputsTestNoUIFactories(
            $this->buildSymbolFactory(),
            $this->buildPopoverFactory(),
            $this->buildLegacyFactory(),
        );
    }

    public function testRenderTextWithFilterContext(): void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $text = $if->text('label', 'byline'); // byline will not be rendered in this context
        $filter = $f->standard("#", "#", "#", "#", "#", "#", [], [], false, false);
        $r = $this->getDefaultRenderer();
        $fr = $r->withAdditionalContext($filter);
        $html = $this->brutallyTrimHTML($fr->render($text));

        $expected = $this->brutallyTrimHTML('
        <div class="col-md-6 col-lg-4 il-popover-container">
            <div class="input-group">
                <label for="id_1" class="input-group-addon leftaddon">label</label>
                <input id="id_1" type="text" class="form-control form-control-sm" />
                <span class="input-group-addon rightaddon">
                    <a class="glyph" href="" aria-label="remove" id="id_2">
                        <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                    </a>
                </span>
            </div>
        </div>
        ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testRenderNumericWithFilterContext(): void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $numeric = $if->numeric('label', 'byline'); // byline will not be rendered in this context
        $filter = $f->standard("#", "#", "#", "#", "#", "#", [], [], false, false);
        $r = $this->getDefaultRenderer();
        $fr = $r->withAdditionalContext($filter);
        $html = $this->brutallyTrimHTML($fr->render($numeric));

        $expected = $this->brutallyTrimHTML('
        <div class="col-md-6 col-lg-4 il-popover-container">
            <div class="input-group">
                <label for="id_1" class="input-group-addon leftaddon">label</label>
                <input id="id_1" type="number" class="form-control form-control-sm" />
                <span class="input-group-addon rightaddon">
                    <a class="glyph" href="" aria-label="remove" id="id_2">
                        <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                    </a>
                </span>
            </div>
        </div>
        ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testRenderSelectWithFilterContext(): void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $options = ["one" => "One", "two" => "Two", "three" => "Three"];
        $select = $if->select('label', $options, 'byline'); // byline will not be rendered in this context
        $filter = $f->standard("#", "#", "#", "#", "#", "#", [], [], false, false);
        $r = $this->getDefaultRenderer();
        $fr = $r->withAdditionalContext($filter);
        $html = $this->brutallyTrimHTML($fr->render($select));

        $expected = $this->brutallyTrimHTML('
        <div class="col-md-6 col-lg-4 il-popover-container">
            <div class="input-group">
                <label for="id_1" class="input-group-addon leftaddon">label</label>
                <select id="id_1">
                    <option selected="selected" value="">-</option>
                    <option value="one">One</option>
                    <option value="two">Two</option>
                    <option value="three">Three</option>
                </select>
                <span class="input-group-addon rightaddon">
                    <a class="glyph" href="" aria-label="remove" id="id_2"><span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span></a>
                </span>
            </div>
        </div>
        ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testRenderMultiSelectWithFilterContext(): void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $options = ["one" => "One", "two" => "Two", "three" => "Three"];
        $multi = $if->multiSelect('label', $options, 'byline'); // byline will not be rendered in this context
        $filter = $f->standard("#", "#", "#", "#", "#", "#", [], [], false, false);
        $r = $this->getDefaultRenderer();
        $fr = $r->withAdditionalContext($filter);
        $html = $this->brutallyTrimHTML($fr->render($multi));

        $expected = $this->brutallyTrimHTML('
        <div class="col-md-6 col-lg-4 il-popover-container">
            <div class="input-group">
                <label class="input-group-addon leftaddon">label</label>
                <span role="button" tabindex="0" class="form-control il-filter-field" id="id_4" data-placement="bottom"></span>
                <div class="il-standard-popover-content" style="display:none;" id="id_2"></div>
                <span class="input-group-addon rightaddon">
                    <a class="glyph" href="" aria-label="remove" id="id_5">
                        <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                    </a>
                </span>
            </div>
            {POPOVER}
        </div>
        ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testRenderDateTimeWithFilterContext(): void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $datetime = $if->dateTime('label', 'byline'); // byline will not be rendered in this context
        $filter = $f->standard("#", "#", "#", "#", "#", "#", [], [], false, false);
        $r = $this->getDefaultRenderer();
        $fr = $r->withAdditionalContext($filter);
        $html = $this->brutallyTrimHTML($fr->render($datetime));

        $expected = $this->brutallyTrimHTML('
        <div class="col-md-6 col-lg-4 il-popover-container">
            <div class="input-group">
                <label for="id_1" class="input-group-addon leftaddon">label</label>
                <div class="input-group date il-input-datetime">
                    <input id="id_1" type="date" class="form-control form-control-sm" />
                </div>
                <span class="input-group-addon rightaddon">
                    <a class="glyph" href="" aria-label="remove" id="id_2">
                        <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                    </a>
                </span>
            </div>
        </div>
        ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testRenderDateTimeWithDurationAndFilterContext(): void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $datetime = $if->dateTime('label', 'byline'); // byline will not be rendered in this context
        $filter = $f->standard("#", "#", "#", "#", "#", "#", [], [], false, false);
        $r = $this->getDefaultRenderer();
        $fr = $r->withAdditionalContext($filter);
        $dr = $fr->withAdditionalContext($datetime);
        $html = $this->brutallyTrimHTML($dr->render($datetime));

        $expected = $this->brutallyTrimHTML('
        <div class="form-group row">
            <label for="id_1" class="control-label col-sm-4 col-md-3 col-lg-2">label</label>
            <div class="col-sm-8 col-md-9 col-lg-10">
                <div class="input-group date il-input-datetime">
                    <input id="id_1" type="date" class="form-control form-control-sm" />
                </div>
            </div>
        </div>
        ');
        $this->assertHTMLEquals($expected, $html);
    }

    public function testRenderDurationWithFilterContext(): void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $datetime = $if->duration('label', 'byline'); // byline will not be rendered in this context
        $filter = $f->standard("#", "#", "#", "#", "#", "#", [], [], false, false);
        $r = $this->getDefaultRenderer();
        $fr = $r->withAdditionalContext($filter);
        $html = $this->brutallyTrimHTML($fr->render($datetime));
        $label_start = 'duration_default_label_start';
        $label_end = 'duration_default_label_end';


        $expected = $this->brutallyTrimHTML('
        <div class="col-md-6 col-lg-4 il-popover-container">
            <div class="input-group">
                <label class="input-group-addon leftaddon">label</label>
                <span role="button" tabindex="0" class="form-control il-filter-field" id="id_7" data-placement="bottom"></span>
                <div class="il-standard-popover-content" style="display:none;" id="id_5"></div>
                <span class="input-group-addon rightaddon">
                    <a class="glyph" href="" aria-label="remove" id="id_8">
                        <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                    </a>
                </span>
            </div>
            {POPOVER}
        </div>
        ');
        $this->assertHTMLEquals($expected, $html);
    }
}
