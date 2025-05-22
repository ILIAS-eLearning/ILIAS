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

class WithNoUIFactories extends NoUIFactory
{
    protected I\Button\Factory $button_factory;
    protected I\Symbol\Factory $symbol_factory;
    protected I\Popover\Factory $popover_factory;
    protected I\Legacy\Factory $legacy_factory;
    protected I\Listing\Factory $listing_factory;

    public function __construct(
        I\Button\Factory $button_factory,
        I\Symbol\Factory $symbol_factory,
        I\Popover\Factory $popover_factory,
        I\Legacy\Factory $legacy_factory,
        I\Listing\Factory $listing_factory
    ) {
        $this->button_factory = $button_factory;
        $this->symbol_factory = $symbol_factory;
        $this->popover_factory = $popover_factory;
        $this->legacy_factory = $legacy_factory;
        $this->listing_factory = $listing_factory;
    }

    public function button(): I\Button\Factory
    {
        return $this->button_factory;
    }

    public function symbol(): I\Symbol\Factory
    {
        return $this->symbol_factory;
    }

    public function popover(): I\Popover\Factory
    {
        return $this->popover_factory;
    }

    public function legacy(): I\Legacy\Factory
    {
        return $this->legacy_factory;
    }

    public function listing(): I\Listing\Factory
    {
        return $this->listing_factory;
    }
}

/**
 * Test on standard filter implementation.
 */

class StandardFilterTest extends ILIAS_UI_TestBase
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
        $language = $this->createMock(ILIAS\Language\Language::class);
        return new I\Input\Field\Factory(
            $this->createMock(\ILIAS\UI\Implementation\Component\Input\UploadLimitResolver::class),
            new I\SignalGenerator(),
            $df,
            new ILIAS\Refinery\Factory($df, $language),
            $language
        );
    }

    protected function buildButtonFactory(): I\Button\Factory
    {
        return new I\Button\Factory();
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
        $mock = $this->createMock(I\Legacy\Factory::class);
        $mock->method('content')->willReturn(
            new I\Legacy\Content('', new I\SignalGenerator())
        );
        return $mock;
    }

    protected function buildListingFactory(): I\Listing\Factory
    {
        return new I\Listing\Factory(
            new I\Listing\Workflow\Factory(),
            new I\Listing\CharacteristicValue\Factory(),
            new I\Listing\Entity\Factory(),
        );
    }

    public function getUIFactory(): WithNoUIFactories
    {
        return new WithNoUIFactories(
            $this->buildButtonFactory(),
            $this->buildSymbolFactory(),
            $this->buildPopoverFactory(),
            $this->buildLegacyFactory(),
            $this->buildListingFactory()
        );
    }

    public function testRenderActivatedCollapsed(): void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $inputs = [
            $if->text("Title"),
            $if->select("Selection", ["one" => "One", "two" => "Two", "three" => "Three"]),
            $if->multiSelect("Multi Selection", ["one" => "Num One", "two" => "Num Two", "three" => "Num Three"])
        ];
        $inputs_rendered = [true, false, true];

        $filter = $f->standard(
            "#",
            "#",
            "#",
            "#",
            "#",
            "#",
            $inputs,
            $inputs_rendered,
            true,
            false
        );

        $r = $this->getDefaultRenderer();
        $html = $r->render($filter);

        $expected = <<<EOT
<div class="il-filter enabled" id="id_1">
    <form class="c-form il-standard-form form-horizontal" enctype="multipart/form-data" method="get" data-cmd-expand="#" data-cmd-collapse="#" data-cmd-apply="#" data-cmd-toggleOn="#" data-cmd-toggleOff="#">
        <div class="il-filter-bar">
            <div class="il-filter-bar-opener">
                <button type="button" aria-expanded="false" aria-controls="active_inputs_id_1 section_inputs_id_1" id="opener_id_1">
                    <span>
                        <span data-collapse-glyph-visibility="0">
                            <a class="glyph" aria-label="collapse_content">
                                <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                            </a>
                        </span>
                        <span data-expand-glyph-visibility="1">
                            <a class="glyph" aria-label="expand_content">
                                <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                            </a>
                        </span> filter
                    </span>
                </button>
            </div>
            <div class="il-filter-bar-toggle">
                <div class="il-toggle-item">
                    <button class="il-toggle-button on" id="id_4" aria-pressed="false">
                        <span class="il-toggle-label-on">toggle_on</span>
                        <span class="il-toggle-label-off">toggle_off</span>
                        <span class="il-toggle-switch"></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="il-filter-inputs-active clearfix" id="active_inputs_id_1" aria-labelledby="opener_id_1" data-active-inputs-expanded="1">
            <span id="1"></span>
            <span id="2"></span>
            <span id="3"></span>
        </div>
        <div class="il-filter-input-section row" id="section_inputs_id_1" aria-labelledby="opener_id_1" data-section-inputs-expanded="0">
            <div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <label for="id_5" class="input-group-addon leftaddon">Title</label>
                    <input id="id_5" type="text" name="filter_input_0/filter_input_1" class="c-field-text" />
                    <span class="input-group-addon rightaddon">
                        <a class="glyph" href="" aria-label="remove" id="id_6">
                            <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                        </a>
                    </span>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <label for="id_7" class="input-group-addon leftaddon">Selection</label>
                    <select id="id_7" name="filter_input_0/filter_input_2">
                        <option selected="selected" value="">-</option>
                        <option value="one">One</option>
                        <option value="two">Two</option>
                        <option value="three">Three</option>
                    </select>
                    <span class="input-group-addon rightaddon">
                        <a class="glyph" href="" aria-label="remove" id="id_8">
                            <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                        </a>
                    </span>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <label class="input-group-addon leftaddon">Multi Selection</label>
                    <span role="button" tabindex="0" class="form-control il-filter-field" id="id_11" data-placement="bottom"></span>
                    <div class="il-standard-popover-content" style="display:none;" id="id_9"></div>
                    <span class="input-group-addon rightaddon">
                        <a class="glyph" href="" aria-label="remove" id="id_12">
                            <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                        </a>
                    </span>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <button class="btn btn-bulky" id="id_18">
                        <span class="glyph" aria-label="add" role="img">
                            <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
                        </span>
                        <span class="bulky-label"></span>
                    </button>
                </div>
                <div class="il-standard-popover-content" style="display:none;" id="id_16"></div>
            </div>
            <div class="il-filter-controls">
                <button class="btn btn-bulky" data-action="" id="id_2">
                    <span class="glyph" role="img">
                        <span class="glyphicon glyphicon-apply" aria-hidden="true"></span>
                    </span>
                    <span class="bulky-label">apply</span>
                </button>
                <button class="btn btn-bulky" data-action="#" id="id_3">
                    <span class="glyph" role="img">
                        <span class="glyphicon glyphicon-reset" aria-hidden="true"></span>
                    </span>
                    <span class="bulky-label">reset</span>
                </button>
            </div>
        </div>
        <input class="il-filter-field-status" type="hidden" name="__filter_status_0" value="1" />
        <input class="il-filter-field-status" type="hidden" name="__filter_status_1" value="0" />
        <input class="il-filter-field-status" type="hidden" name="__filter_status_2" value="1" />
    </form>
</div>
EOT;

        $this->assertHTMLEquals($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($html));
    }

    public function testRenderDeactivatedCollapsed(): void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $inputs = [
            $if->text("Title"),
            $if->select("Selection", ["one" => "One", "two" => "Two", "three" => "Three"]),
            $if->multiSelect("Multi Selection", ["one" => "Num One", "two" => "Num Two", "three" => "Num Three"])
        ];
        $inputs_rendered = [true, false, true];

        $filter = $f->standard(
            "#",
            "#",
            "#",
            "#",
            "#",
            "#",
            $inputs,
            $inputs_rendered,
            false,
            false
        );

        $r = $this->getDefaultRenderer();
        $html = $r->render($filter);

        $expected = <<<EOT
<div class="il-filter disabled" id="id_1">
    <form class="c-form il-standard-form form-horizontal" enctype="multipart/form-data" method="get" data-cmd-expand="#" data-cmd-collapse="#" data-cmd-apply="#" data-cmd-toggleOn="#" data-cmd-toggleOff="#">
        <div class="il-filter-bar">
            <div class="il-filter-bar-opener">
                <button type="button" aria-expanded="false" aria-controls="active_inputs_id_1 section_inputs_id_1" id="opener_id_1">
                    <span>
                        <span data-collapse-glyph-visibility="0">
                            <a class="glyph" aria-label="collapse_content">
                                <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                            </a>
                        </span>
                        <span data-expand-glyph-visibility="1">
                            <a class="glyph" aria-label="expand_content">
                                <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                            </a>
                        </span> filter
                    </span>
                </button>
            </div>
            <div class="il-filter-bar-toggle">
                <div class="il-toggle-item">
                    <button class="il-toggle-button off" id="id_4" aria-pressed="false">
                        <span class="il-toggle-label-on">toggle_on</span>
                        <span class="il-toggle-label-off">toggle_off</span>
                        <span class="il-toggle-switch"></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="il-filter-inputs-active clearfix" id="active_inputs_id_1" aria-labelledby="opener_id_1" data-active-inputs-expanded="1">
            <span id="1"></span>
            <span id="2"></span>
            <span id="3"></span>
        </div>
        <div class="il-filter-input-section row" id="section_inputs_id_1" aria-labelledby="opener_id_1" data-section-inputs-expanded="0">
            <div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <label for="id_5" class="input-group-addon leftaddon">Title</label>
                    <input id="id_5" type="text" name="filter_input_0/filter_input_1" class="c-field-text" />
                    <span class="input-group-addon rightaddon">
                        <a class="glyph" href="" aria-label="remove" id="id_6">
                            <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                        </a>
                    </span>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <label for="id_7" class="input-group-addon leftaddon">Selection</label>
                    <select id="id_7" name="filter_input_0/filter_input_2">
                        <option selected="selected" value="">-</option>
                        <option value="one">One</option>
                        <option value="two">Two</option>
                        <option value="three">Three</option>
                    </select>
                    <span class="input-group-addon rightaddon">
                        <a class="glyph" href="" aria-label="remove" id="id_8">
                            <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                        </a>
                    </span>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <label class="input-group-addon leftaddon">Multi Selection</label>
                    <span role="button" tabindex="0" class="form-control il-filter-field" id="id_11" data-placement="bottom"></span>
                    <div class="il-standard-popover-content" style="display:none;" id="id_9"></div>
                    <span class="input-group-addon rightaddon">
                        <a class="glyph" href="" aria-label="remove" id="id_12">
                            <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                        </a>
                    </span>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <button class="btn btn-bulky" id="id_18">
                        <span class="glyph" aria-label="add" role="img">
                            <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
                        </span>
                        <span class="bulky-label"></span>
                    </button>
                </div>
                <div class="il-standard-popover-content" style="display:none;" id="id_16"></div>
            </div>
            <div class="il-filter-controls">
                <button class="btn btn-bulky" data-action="" id="id_2">
                    <span class="glyph" role="img">
                        <span class="glyphicon glyphicon-apply" aria-hidden="true"></span>
                    </span>
                    <span class="bulky-label">apply</span>
                </button>
                <button class="btn btn-bulky" data-action="#" id="id_3">
                    <span class="glyph" role="img">
                        <span class="glyphicon glyphicon-reset" aria-hidden="true"></span>
                    </span>
                    <span class="bulky-label">reset</span>
                </button>
            </div>
        </div>
        <input class="il-filter-field-status" type="hidden" name="__filter_status_0" value="1" />
        <input class="il-filter-field-status" type="hidden" name="__filter_status_1" value="0" />
        <input class="il-filter-field-status" type="hidden" name="__filter_status_2" value="1" />
    </form>
</div>
EOT;

        $this->assertHTMLEquals($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($html));
    }

    public function testRenderActivatedExpanded(): void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $inputs = [
            $if->text("Title"),
            $if->select("Selection", ["one" => "One", "two" => "Two", "three" => "Three"]),
            $if->multiSelect("Multi Selection", ["one" => "Num One", "two" => "Num Two", "three" => "Num Three"])
        ];
        $inputs_rendered = [true, false, true];

        $filter = $f->standard(
            "#",
            "#",
            "#",
            "#",
            "#",
            "#",
            $inputs,
            $inputs_rendered,
            true,
            true
        );

        $r = $this->getDefaultRenderer();
        $html = $r->render($filter);

        $expected = <<<EOT
<div class="il-filter enabled" id="id_1">
    <form class="c-form il-standard-form form-horizontal" enctype="multipart/form-data" method="get" data-cmd-expand="#" data-cmd-collapse="#" data-cmd-apply="#" data-cmd-toggleOn="#" data-cmd-toggleOff="#">
        <div class="il-filter-bar">
            <div class="il-filter-bar-opener">
                <button type="button" aria-expanded="true" aria-controls="active_inputs_id_1 section_inputs_id_1" id="opener_id_1">
                    <span>
                        <span data-collapse-glyph-visibility="1">
                            <a class="glyph" aria-label="collapse_content">
                                <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                            </a>
                        </span>
                        <span data-expand-glyph-visibility="0">
                            <a class="glyph" aria-label="expand_content">
                                <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                            </a>
                        </span> filter
                    </span>
                </button>
            </div>
            <div class="il-filter-bar-toggle">
                <div class="il-toggle-item">
                    <button class="il-toggle-button on" id="id_4" aria-pressed="false">
                        <span class="il-toggle-label-on">toggle_on</span>
                        <span class="il-toggle-label-off">toggle_off</span>
                        <span class="il-toggle-switch"></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="il-filter-inputs-active clearfix" id="active_inputs_id_1" aria-labelledby="opener_id_1" data-active-inputs-expanded="0">
            <span id="1"></span>
            <span id="2"></span>
            <span id="3"></span>
        </div>
        <div class="il-filter-input-section row" id="section_inputs_id_1" aria-labelledby="opener_id_1" data-section-inputs-expanded="1">
            <div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <label for="id_5" class="input-group-addon leftaddon">Title</label>
                    <input id="id_5" type="text" name="filter_input_0/filter_input_1" class="c-field-text" />
                    <span class="input-group-addon rightaddon">
                        <a class="glyph" href="" aria-label="remove" id="id_6">
                            <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                        </a>
                    </span>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <label for="id_7" class="input-group-addon leftaddon">Selection</label>
                    <select id="id_7" name="filter_input_0/filter_input_2">
                        <option selected="selected" value="">-</option>
                        <option value="one">One</option>
                        <option value="two">Two</option>
                        <option value="three">Three</option>
                    </select>
                    <span class="input-group-addon rightaddon">
                        <a class="glyph" href="" aria-label="remove" id="id_8">
                            <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                        </a>
                    </span>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <label class="input-group-addon leftaddon">Multi Selection</label>
                    <span role="button" tabindex="0" class="form-control il-filter-field" id="id_11" data-placement="bottom"></span>
                    <div class="il-standard-popover-content" style="display:none;" id="id_9"></div>
                    <span class="input-group-addon rightaddon">
                        <a class="glyph" href="" aria-label="remove" id="id_12">
                            <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                        </a>
                    </span>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <button class="btn btn-bulky" id="id_18">
                        <span class="glyph" aria-label="add" role="img">
                            <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
                        </span>
                        <span class="bulky-label"></span>
                    </button>
                </div>
                <div class="il-standard-popover-content" style="display:none;" id="id_16"></div>
            </div>
            <div class="il-filter-controls">
                <button class="btn btn-bulky" data-action="" id="id_2">
                    <span class="glyph" role="img">
                        <span class="glyphicon glyphicon-apply" aria-hidden="true"></span>
                    </span>
                    <span class="bulky-label">apply</span>
                </button>
                <button class="btn btn-bulky" data-action="#" id="id_3">
                    <span class="glyph" role="img">
                        <span class="glyphicon glyphicon-reset" aria-hidden="true"></span>
                    </span>
                    <span class="bulky-label">reset</span>
                </button>
            </div>
        </div>
        <input class="il-filter-field-status" type="hidden" name="__filter_status_0" value="1" />
        <input class="il-filter-field-status" type="hidden" name="__filter_status_1" value="0" />
        <input class="il-filter-field-status" type="hidden" name="__filter_status_2" value="1" />
    </form>
</div>
EOT;

        $this->assertHTMLEquals($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($html));
    }

    public function testRenderDeactivatedExpanded(): void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $inputs = [
            $if->text("Title"),
            $if->select("Selection", ["one" => "One", "two" => "Two", "three" => "Three"]),
            $if->multiSelect("Multi Selection", ["one" => "Num One", "two" => "Num Two", "three" => "Num Three"])
        ];
        $inputs_rendered = [true, false, true];

        $filter = $f->standard(
            "#",
            "#",
            "#",
            "#",
            "#",
            "#",
            $inputs,
            $inputs_rendered,
            false,
            true
        );

        $r = $this->getDefaultRenderer();
        $html = $r->render($filter);

        $expected = <<<EOT
<div class="il-filter disabled" id="id_1">
    <form class="c-form il-standard-form form-horizontal" enctype="multipart/form-data" method="get" data-cmd-expand="#" data-cmd-collapse="#" data-cmd-apply="#" data-cmd-toggleOn="#" data-cmd-toggleOff="#">
        <div class="il-filter-bar">
            <div class="il-filter-bar-opener">
                <button type="button" aria-expanded="true" aria-controls="active_inputs_id_1 section_inputs_id_1" id="opener_id_1">
                    <span>
                        <span data-collapse-glyph-visibility="1">
                            <a class="glyph" aria-label="collapse_content">
                                <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                            </a>
                        </span>
                        <span data-expand-glyph-visibility="0">
                            <a class="glyph" aria-label="expand_content">
                                <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                            </a>
                        </span> filter
                    </span>
                </button>
            </div>
            <div class="il-filter-bar-toggle">
                <div class="il-toggle-item">
                    <button class="il-toggle-button off" id="id_4" aria-pressed="false">
                        <span class="il-toggle-label-on">toggle_on</span>
                        <span class="il-toggle-label-off">toggle_off</span>
                        <span class="il-toggle-switch"></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="il-filter-inputs-active clearfix" id="active_inputs_id_1" aria-labelledby="opener_id_1" data-active-inputs-expanded="0">
            <span id="1"></span>
            <span id="2"></span>
            <span id="3"></span>
        </div>
        <div class="il-filter-input-section row" id="section_inputs_id_1" aria-labelledby="opener_id_1" data-section-inputs-expanded="1">
            <div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <label for="id_5" class="input-group-addon leftaddon">Title</label>
                    <input id="id_5" type="text" name="filter_input_0/filter_input_1" class="c-field-text" />
                    <span class="input-group-addon rightaddon">
                        <a class="glyph" href="" aria-label="remove" id="id_6">
                            <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                        </a>
                    </span>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <label for="id_7" class="input-group-addon leftaddon">Selection</label>
                    <select id="id_7" name="filter_input_0/filter_input_2">
                        <option selected="selected" value="">-</option>
                        <option value="one">One</option>
                        <option value="two">Two</option>
                        <option value="three">Three</option>
                    </select>
                    <span class="input-group-addon rightaddon">
                        <a class="glyph" href="" aria-label="remove" id="id_8">
                            <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                        </a>
                    </span>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <label class="input-group-addon leftaddon">Multi Selection</label>
                    <span role="button" tabindex="0" class="form-control il-filter-field" id="id_11" data-placement="bottom"></span>
                    <div class="il-standard-popover-content" style="display:none;" id="id_9"></div>
                    <span class="input-group-addon rightaddon">
                        <a class="glyph" href="" aria-label="remove" id="id_12">
                            <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                        </a>
                    </span>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <button class="btn btn-bulky" id="id_18">
                        <span class="glyph" aria-label="add" role="img">
                            <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
                        </span>
                        <span class="bulky-label"></span>
                    </button>
                </div>
                <div class="il-standard-popover-content" style="display:none;" id="id_16"></div>
            </div>
            <div class="il-filter-controls">
                <button class="btn btn-bulky" data-action="" id="id_2">
                    <span class="glyph" role="img">
                        <span class="glyphicon glyphicon-apply" aria-hidden="true"></span>
                    </span>
                    <span class="bulky-label">apply</span>
                </button>
                <button class="btn btn-bulky" data-action="#" id="id_3">
                    <span class="glyph" role="img">
                        <span class="glyphicon glyphicon-reset" aria-hidden="true"></span>
                    </span>
                    <span class="bulky-label">reset</span>
                </button>
            </div>
        </div>
        <input class="il-filter-field-status" type="hidden" name="__filter_status_0" value="1" />
        <input class="il-filter-field-status" type="hidden" name="__filter_status_1" value="0" />
        <input class="il-filter-field-status" type="hidden" name="__filter_status_2" value="1" />
    </form>
</div>
EOT;

        $this->assertHTMLEquals($this->brutallyTrimHTML($expected), $this->brutallyTrimHTML($html));
    }

    public function testDedicatedNames(): void
    {
        $f = $this->buildFactory();
        $if = $this->buildInputFactory();
        $inputs = [
            $if->text("Title")->withDedicatedName('title'),
            $if->select("Selection", ["one" => "One", "two" => "Two", "three" => "Three"])->withDedicatedName('selection'),
            $if->multiSelect("Multi Selection", ["one" => "Num One", "two" => "Num Two", "three" => "Num Three"])
        ];
        $filter = $f->standard(
            "#",
            "#",
            "#",
            "#",
            "#",
            "#",
            $inputs,
            [true, true, true],
            true,
            true
        );

        $inputs = $filter->getInputs();
        $this->assertEquals('filter_input_0/title', $inputs[0]->getName());
        $this->assertEquals('filter_input_0/selection', $inputs[1]->getName());
        $this->assertEquals('filter_input_0/filter_input_1', $inputs[2]->getName());
    }
}
