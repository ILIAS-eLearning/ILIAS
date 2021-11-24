<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../../Base.php");
require_once(__DIR__ . "/FilterTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;
use \ILIAS\Data;
use ILIAS\Refinery;

class WithNoUIFactories extends NoUIFactory
{
    protected $button_factory;
    protected $symbol_factory;
    protected $popover_factory;
    protected $legacy_factory;
    protected $listing_factory;


    public function __construct($button_factory, $symbol_factory, $popover_factory, $legacy_factory, $listing_factory)
    {
        $this->button_factory = $button_factory;
        $this->symbol_factory = $symbol_factory;
        $this->popover_factory = $popover_factory;
        $this->legacy_factory = $legacy_factory;
        $this->listing_factory = $listing_factory;
    }


    public function button()
    {
        return $this->button_factory;
    }

    public function symbol() : \ILIAS\UI\Component\Symbol\Factory
    {
        return $this->symbol_factory;
    }

    public function popover()
    {
        return $this->popover_factory;
    }

    public function legacy($content)
    {
        return $this->legacy_factory->legacy("");
    }

    public function listing()
    {
        return $this->listing_factory;
    }
}

/**
 * Test on standard filter implementation.
 */

class StandardFilterTest extends ILIAS_UI_TestBase
{
    protected function buildFactory()
    {
        return new ILIAS\UI\Implementation\Component\Input\Container\Filter\Factory(
            new SignalGenerator(),
            $this->buildInputFactory()
        );
    }

    protected function buildInputFactory()
    {
        $df = new Data\Factory();
        $language = $this->createMock(\ilLanguage::class);
        return new ILIAS\UI\Implementation\Component\Input\Field\Factory(
            new SignalGenerator(),
            $df,
            new ILIAS\Refinery\Factory($df, $language),
            $language
        );
    }

    protected function buildButtonFactory()
    {
        return new ILIAS\UI\Implementation\Component\Button\Factory;
    }

    protected function buildSymbolFactory()
    {
        return new ILIAS\UI\Implementation\Component\Symbol\Factory(
            new ILIAS\UI\Implementation\Component\Symbol\Icon\Factory,
            new ILIAS\UI\Implementation\Component\Symbol\Glyph\Factory,
            new ILIAS\UI\Implementation\Component\Symbol\Avatar\Factory
        );
    }

    protected function buildPopoverFactory()
    {
        return new ILIAS\UI\Implementation\Component\Popover\Factory(new SignalGenerator());
    }

    protected function buildLegacyFactory()
    {
        return new ILIAS\UI\Implementation\Component\Legacy\Factory(new SignalGenerator());
    }

    protected function buildListingFactory()
    {
        return new ILIAS\UI\Implementation\Component\Listing\Factory;
    }

    public function getUIFactory()
    {
        return new WithNoUIFactories(
            $this->buildButtonFactory(),
            $this->buildSymbolFactory(),
            $this->buildPopoverFactory(),
            $this->buildLegacyFactory(),
            $this->buildListingFactory()
        );
    }

    public function test_render_activated_collapsed()
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
    <form class="il-standard-form form-horizontal" enctype="multipart/formdata" method="get" novalidate="novalidate" data-cmd-expand="#" data-cmd-collapse="#" data-cmd-apply="#" data-cmd-toggleOn="#" data-cmd-toggleOff="#">
        <div class="il-filter-bar">
		<span class="il-filter-bar-opener" data-toggle="collapse" data-target=".il-filter-inputs-active,.il-filter-input-section" aria-expanded="false">
			<button class="btn btn-bulky" data-action="" id="id_2">
				<span class="glyph" aria-label="collapse_content" role="img">
				    <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                </span>
				<span class="bulky-label">filter</span>
			</button>
			<button class="btn btn-bulky" data-action="" id="id_3">
				<span class="glyph" aria-label="expand_content" role="img">
				    <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                </span>
				<span class="bulky-label">filter</span>
			</button>
		</span>
		<span class="il-filter-bar-toggle">
			<button class="il-toggle-button on" id="id_6" aria-pressed="false">
				<span class="il-toggle-label-on">toggle_on</span>
				<span class="il-toggle-label-off">toggle_off</span>
				<span class="il-toggle-switch"></span>
			</button>
		</span>
        </div>
        <div class="il-filter-inputs-active clearfix collapse in">
            <span id="1"></span>
            <span id="2"></span>
            <span id="3"></span>
        </div>
        <div class="il-filter-input-section row collapse ">
			<div class="col-md-6 col-lg-4 il-popover-container">
				<div class="input-group">
					<label for="id_7" class="input-group-addon leftaddon">Title</label>
					<input id="id_7" type="text" name="filter_input_1" class="form-control form-control-sm" />
					<span class="input-group-addon rightaddon">
						<a class="glyph" href="" aria-label="remove" id="id_8">
							<span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
						</a>
					</span>
				</div>
			</div>
			<div class="col-md-6 col-lg-4 il-popover-container">
				<div class="input-group">
					<label for="id_9" class="input-group-addon leftaddon">Selection</label>
					<select id="id_9" name="filter_input_2">
                        <option selected="selected" value="">-</option>
                        <option value="one">One</option>
                        <option value="two">Two</option>
                        <option value="three">Three</option>
                    </select>
					<span class="input-group-addon rightaddon">
						<a class="glyph" href="" aria-label="remove" id="id_10">
							<span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
						</a>
					</span>
				</div>
			</div>
			<div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <label class="input-group-addon leftaddon">Multi Selection</label>
                    <span role="button" tabindex="0" class="form-control il-filter-field" id="id_14" data-placement="bottom"></span>
                    <div class="il-standard-popover-content" style="display:none;" id="id_12"></div>
                    <span class="input-group-addon rightaddon">
                        <a class="glyph" href="" aria-label="remove" id="id_15">
                            <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                        </a>
                    </span>
                </div>
            </div>
			<div class="col-md-6 col-lg-4 il-popover-container">
    			<div class="input-group">
					<button class="btn btn-bulky" id="id_21">
        				<span class="glyph" aria-label="add" role="img">
							<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
						</span>
					    <span class="bulky-label"></span>
					</button>
    			</div>
    			<div class="il-standard-popover-content" style="display:none;" id="id_19"></div>
			</div>
			<div class="il-filter-controls">
			    <button class="btn btn-bulky" data-action="" id="id_4">
			        <span class="glyph" aria-label="apply" role="img">
			            <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    </span>
                    <span class="bulky-label">apply</span>
                </button>
                <button class="btn btn-bulky" data-action="#" id="id_5">
                    <span class="glyph" aria-label="reset" role="img">
                        <span class="glyphicon glyphicon-repeat" aria-hidden="true"></span>
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

    public function test_render_deactivated_collapsed()
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
    <form class="il-standard-form form-horizontal" enctype="multipart/formdata" method="get" novalidate="novalidate" data-cmd-expand="#" data-cmd-collapse="#" data-cmd-apply="#" data-cmd-toggleOn="#" data-cmd-toggleOff="#">
        <div class="il-filter-bar">
		<span class="il-filter-bar-opener" data-toggle="collapse" data-target=".il-filter-inputs-active,.il-filter-input-section" aria-expanded="false">
			<button class="btn btn-bulky" data-action="" id="id_2">
				<span class="glyph" aria-label="collapse_content" role="img">
				    <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                </span>
				<span class="bulky-label">filter</span>
			</button>
			<button class="btn btn-bulky" data-action="" id="id_3">
				<span class="glyph" aria-label="expand_content" role="img">
				    <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                </span>
				<span class="bulky-label">filter</span>
			</button>
		</span>
		<span class="il-filter-bar-toggle">
			<button class="il-toggle-button off" id="id_6" aria-pressed="false">
				<span class="il-toggle-label-on">toggle_on</span>
				<span class="il-toggle-label-off">toggle_off</span>
				<span class="il-toggle-switch"></span>
			</button>
		</span>
        </div>
        <div class="il-filter-inputs-active clearfix collapse in">
            <span id="1"></span>
            <span id="2"></span>
            <span id="3"></span>
        </div>
        <div class="il-filter-input-section row collapse ">
			<div class="col-md-6 col-lg-4 il-popover-container">
				<div class="input-group">
					<label for="id_7" class="input-group-addon leftaddon">Title</label>
					<input id="id_7" type="text" name="filter_input_1" class="form-control form-control-sm" />
					<span class="input-group-addon rightaddon">
						<a class="glyph" href="" aria-label="remove" id="id_8">
							<span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
						</a>
					</span>
				</div>
			</div>
			<div class="col-md-6 col-lg-4 il-popover-container">
				<div class="input-group">
					<label for="id_9" class="input-group-addon leftaddon">Selection</label>
					<select id="id_9" name="filter_input_2">
                        <option selected="selected" value="">-</option>
                        <option value="one">One</option>
                        <option value="two">Two</option>
                        <option value="three">Three</option>
                    </select>
					<span class="input-group-addon rightaddon">
						<a class="glyph" href="" aria-label="remove" id="id_10">
							<span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
						</a>
					</span>
				</div>
			</div>
			<div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <label class="input-group-addon leftaddon">Multi Selection</label>
                    <span role="button" tabindex="0" class="form-control il-filter-field" id="id_14" data-placement="bottom"></span>
                    <div class="il-standard-popover-content" style="display:none;" id="id_12"></div>
                    <span class="input-group-addon rightaddon">
                        <a class="glyph" href="" aria-label="remove" id="id_15">
                            <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                        </a>
                    </span>
                </div>
            </div>
			<div class="col-md-6 col-lg-4 il-popover-container">
    			<div class="input-group">
					<button class="btn btn-bulky" id="id_21">
        				<span class="glyph" aria-label="add" role="img">
							<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
						</span>
					    <span class="bulky-label"></span>
					</button>
    			</div>
    			<div class="il-standard-popover-content" style="display:none;" id="id_19"></div>
			</div>
			<div class="il-filter-controls">
			    <button class="btn btn-bulky" data-action="" id="id_4">
			        <span class="glyph" aria-label="apply" role="img">
			            <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    </span>
                    <span class="bulky-label">apply</span>
                </button>
                <button class="btn btn-bulky" data-action="#" id="id_5">
                    <span class="glyph" aria-label="reset" role="img">
                        <span class="glyphicon glyphicon-repeat" aria-hidden="true"></span>
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

    public function test_render_activated_expanded()
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
    <form class="il-standard-form form-horizontal" enctype="multipart/formdata" method="get" novalidate="novalidate" data-cmd-expand="#" data-cmd-collapse="#" data-cmd-apply="#" data-cmd-toggleOn="#" data-cmd-toggleOff="#">
        <div class="il-filter-bar">
		<span class="il-filter-bar-opener" data-toggle="collapse" data-target=".il-filter-inputs-active,.il-filter-input-section" aria-expanded="true">
			<button class="btn btn-bulky" data-action="" id="id_2">
				<span class="glyph" aria-label="expand_content" role="img">
				    <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                </span>
				<span class="bulky-label">filter</span>
			</button>
			<button class="btn btn-bulky" data-action="" id="id_3">
				<span class="glyph" aria-label="collapse_content" role="img">
				    <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                </span>
				<span class="bulky-label">filter</span>
			</button>
		</span>
		<span class="il-filter-bar-toggle">
			<button class="il-toggle-button on" id="id_6" aria-pressed="false">
				<span class="il-toggle-label-on">toggle_on</span>
				<span class="il-toggle-label-off">toggle_off</span>
				<span class="il-toggle-switch"></span>
			</button>
		</span>
        </div>
        <div class="il-filter-inputs-active clearfix collapse ">
            <span id="1"></span>
            <span id="2"></span>
            <span id="3"></span>
        </div>
        <div class="il-filter-input-section row collapse in">
			<div class="col-md-6 col-lg-4 il-popover-container">
				<div class="input-group">
					<label for="id_7" class="input-group-addon leftaddon">Title</label>
					<input id="id_7" type="text" name="filter_input_1" class="form-control form-control-sm" />
					<span class="input-group-addon rightaddon">
						<a class="glyph" href="" aria-label="remove" id="id_8">
							<span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
						</a>
					</span>
				</div>
			</div>
			<div class="col-md-6 col-lg-4 il-popover-container">
				<div class="input-group">
					<label for="id_9" class="input-group-addon leftaddon">Selection</label>
					<select id="id_9" name="filter_input_2">
                        <option selected="selected" value="">-</option>
                        <option value="one">One</option>
                        <option value="two">Two</option>
                        <option value="three">Three</option>
                    </select>
					<span class="input-group-addon rightaddon">
						<a class="glyph" href="" aria-label="remove" id="id_10">
							<span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
						</a>
					</span>
				</div>
			</div>
			<div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <label class="input-group-addon leftaddon">Multi Selection</label>
                    <span role="button" tabindex="0" class="form-control il-filter-field" id="id_14" data-placement="bottom"></span>
                    <div class="il-standard-popover-content" style="display:none;" id="id_12"></div>
                    <span class="input-group-addon rightaddon">
                        <a class="glyph" href="" aria-label="remove" id="id_15">
                            <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                        </a>
                    </span>
                </div>
            </div>
			<div class="col-md-6 col-lg-4 il-popover-container">
    			<div class="input-group">
					<button class="btn btn-bulky" id="id_21">
        				<span class="glyph" aria-label="add" role="img">
							<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
						</span>
					    <span class="bulky-label"></span>
					</button>
    			</div>
    			<div class="il-standard-popover-content" style="display:none;" id="id_19"></div>
			</div>
			<div class="il-filter-controls">
			    <button class="btn btn-bulky" data-action="" id="id_4">
			        <span class="glyph" aria-label="apply" role="img">
			            <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    </span>
                    <span class="bulky-label">apply</span>
                </button>
                <button class="btn btn-bulky" data-action="#" id="id_5">
                    <span class="glyph" aria-label="reset" role="img">
                        <span class="glyphicon glyphicon-repeat" aria-hidden="true"></span>
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

    public function test_render_deactivated_expanded()
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
    <form class="il-standard-form form-horizontal" enctype="multipart/formdata" method="get" novalidate="novalidate" data-cmd-expand="#" data-cmd-collapse="#" data-cmd-apply="#" data-cmd-toggleOn="#" data-cmd-toggleOff="#">
        <div class="il-filter-bar">
		<span class="il-filter-bar-opener" data-toggle="collapse" data-target=".il-filter-inputs-active,.il-filter-input-section" aria-expanded="true">
			<button class="btn btn-bulky" data-action="" id="id_2">
				<span class="glyph" aria-label="expand_content" role="img">
				    <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                </span>
				<span class="bulky-label">filter</span>
			</button>
			<button class="btn btn-bulky" data-action="" id="id_3">
				<span class="glyph" aria-label="collapse_content" role="img">
				    <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                </span>
				<span class="bulky-label">filter</span>
			</button>
		</span>
		<span class="il-filter-bar-toggle">
			<button class="il-toggle-button off" id="id_6" aria-pressed="false">
				<span class="il-toggle-label-on">toggle_on</span>
				<span class="il-toggle-label-off">toggle_off</span>
				<span class="il-toggle-switch"></span>
			</button>
		</span>
        </div>
        <div class="il-filter-inputs-active clearfix collapse ">
            <span id="1"></span>
            <span id="2"></span>
            <span id="3"></span>
        </div>
        <div class="il-filter-input-section row collapse in">
			<div class="col-md-6 col-lg-4 il-popover-container">
				<div class="input-group">
					<label for="id_7" class="input-group-addon leftaddon">Title</label>
					<input id="id_7" type="text" name="filter_input_1" class="form-control form-control-sm" />
					<span class="input-group-addon rightaddon">
						<a class="glyph" href="" aria-label="remove" id="id_8">
							<span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
						</a>
					</span>
				</div>
			</div>
			<div class="col-md-6 col-lg-4 il-popover-container">
				<div class="input-group">
					<label for="id_9" class="input-group-addon leftaddon">Selection</label>
					<select id="id_9" name="filter_input_2">
                        <option selected="selected" value="">-</option>
                        <option value="one">One</option>
                        <option value="two">Two</option>
                        <option value="three">Three</option>
                    </select>
					<span class="input-group-addon rightaddon">
						<a class="glyph" href="" aria-label="remove" id="id_10">
							<span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
						</a>
					</span>
				</div>
			</div>
			<div class="col-md-6 col-lg-4 il-popover-container">
                <div class="input-group">
                    <label class="input-group-addon leftaddon">Multi Selection</label>
                    <span role="button" tabindex="0" class="form-control il-filter-field" id="id_14" data-placement="bottom"></span>
                    <div class="il-standard-popover-content" style="display:none;" id="id_12"></div>
                    <span class="input-group-addon rightaddon">
                        <a class="glyph" href="" aria-label="remove" id="id_15">
                            <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                        </a>
                    </span>
                </div>
            </div>
			<div class="col-md-6 col-lg-4 il-popover-container">
    			<div class="input-group">
					<button class="btn btn-bulky" id="id_21">
        				<span class="glyph" aria-label="add" role="img">
							<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
						</span>
					    <span class="bulky-label"></span>
					</button>
    			</div>
    			<div class="il-standard-popover-content" style="display:none;" id="id_19"></div>
			</div>
			<div class="il-filter-controls">
			    <button class="btn btn-bulky" data-action="" id="id_4">
			        <span class="glyph" aria-label="apply" role="img">
			            <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    </span>
                    <span class="bulky-label">apply</span>
                </button>
                <button class="btn btn-bulky" data-action="#" id="id_5">
                    <span class="glyph" aria-label="reset" role="img">
                        <span class="glyphicon glyphicon-repeat" aria-hidden="true"></span>
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
}
