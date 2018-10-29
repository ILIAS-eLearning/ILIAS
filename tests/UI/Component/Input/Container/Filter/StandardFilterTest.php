<?php

/* Copyright (c) 2018 Thomas Famula <famula@leifos.de> Extended GPL, see docs/LICENSE */

require_once(__DIR__ . "/../../../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../../../Base.php");
require_once(__DIR__ . "/FilterTest.php");

use ILIAS\UI\Implementation\Component\SignalGenerator;

class WithNoUIFactories extends NoUIFactory {

	protected $button_factory;
	protected $glyph_factory;
	protected $popover_factory;
	protected $legacy_factory;
	protected $listing_factory;


	public function __construct($button_factory, $glyph_factory, $popover_factory, $legacy_factory, $listing_factory) {
		$this->button_factory = $button_factory;
		$this->glyph_factory = $glyph_factory;
		$this->popover_factory = $popover_factory;
		$this->legacy_factory = $legacy_factory;
		$this->listing_factory = $listing_factory;
	}


	public function button() {
		return $this->button_factory;
	}

	public function glyph() {
		return $this->glyph_factory;
	}

	public function popover()
	{
		return $this->popover_factory;
	}

	public function legacy($content)
	{
		return $this->legacy_factory;
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

	protected function buildFactory() {
		return new ILIAS\UI\Implementation\Component\Input\Container\Filter\Factory();
	}

	protected function buildInputFactory() {
		return new ILIAS\UI\Implementation\Component\Input\Field\Factory(new SignalGenerator());
	}

	protected function buildButtonFactory() {
		return new ILIAS\UI\Implementation\Component\Button\Factory;
	}

	protected function buildGlyphFactory() {
		return new ILIAS\UI\Implementation\Component\Glyph\Factory;
	}

	protected function buildPopoverFactory() {
		return new ILIAS\UI\Implementation\Component\Popover\Factory(new SignalGenerator());
	}

	protected function buildLegacyFactory() {
		return new ILIAS\UI\Implementation\Component\Legacy\Legacy("");
	}

	protected function buildListingFactory() {
		return new ILIAS\UI\Implementation\Component\Listing\Factory;
	}

	public function getUIFactory() {
		return new WithNoUIFactories($this->buildButtonFactory(), $this->buildGlyphFactory(), $this->buildPopoverFactory(),
			$this->buildLegacyFactory(), $this->buildListingFactory());
	}

	public function test_render_activated_collapsed() {

		$f = $this->buildFactory();
		$if = $this->buildInputFactory();
		$inputs = [$if->text("Title"), $if->select("Selection", ["One", "Two", "Three"])];
		$inputs_rendered = [true, false];

		$filter = $f->standard("#", "#", "#", "#",
			"#", "#", $inputs, $inputs_rendered, true, false);

		$r = $this->getDefaultRenderer();
		$html = $r->render($filter);

		$expected = <<<EOT
<div class="il-filter  enabled  collapsed">
    <form class="il-standard-form form-horizontal" enctype="multipart/formdata" action="" method="post" novalidate="novalidate">
        <div class="il-filter-bar">
            <span class="il-filter-bar-opener"  aria-expanded="false">
            	<button class="btn btn-bulky" data-action="" id="id_1"   aria-pressed="undefined" >
            		<span class="glyph" aria-label="expand_content">
						<span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
					</span>
					<div><span class="bulky-label">filter</span></div>
				</button>
 			</span>
            <span class="il-filter-bar-opener-disabled"> filter </span>
            <span class="il-filter-bar-controls">
            	<button class="btn btn-bulky" data-action="" id="id_2"   aria-pressed="undefined" >
            		<span class="glyph" aria-label="apply">
						<span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
					</span>
					<div><span class="bulky-label">apply</span></div>
				</button>
 				<button class="btn btn-bulky" data-action="#" id="id_3"   aria-pressed="undefined" >
 					<span class="glyph" aria-label="reset">
						<span class="glyphicon glyphicon-repeat" aria-hidden="true"></span>
					</span>
					<div><span class="bulky-label">reset</span></div>
				</button>
				<button class="il-toggle-button on" id="id_4" aria-pressed="false">
    				<div class="il-toggle-switch"></div>
				</button>
 			</span>
        </div>
        <div class="il-filter-inputs-active clearfix"> <span id="1"> </span>  <span id="2"> </span> </div>
        <div id="inputs" class="il-filter-input-section form-group row">
			<div class="col-md-4 il-popover-container">
				<div class="input-group">
					<span class="input-group-addon leftaddon">Title</span>
					<span role="button" tabindex="0" class="form-control il-filter-field" id="id_7" data-placement="bottom"></span>
					<div class="il-standard-popover-content" style="display:none;" id="id_6"></div>
					<span class="input-group-addon rightaddon">
						<a class="glyph" aria-label="remove" id="id_8">
							<span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
						</a>
					</span>
				</div>
			</div>
			<div class="col-md-4 il-popover-container">
				<div class="input-group">
					<span class="input-group-addon leftaddon">Selection</span>
					<span role="button" tabindex="0" class="form-control il-filter-field" id="id_11" data-placement="bottom"></span>
					<div class="il-standard-popover-content" style="display:none;" id="id_10"></div>
					<span class="input-group-addon rightaddon">
						<a class="glyph" aria-label="remove" id="id_12">
							<span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
						</a>
					</span>
				</div> 
			</div>
			<div class="col-md-4 il-popover-container">
    			<div class="input-group">
        			<button class="btn btn-bulky" id="id_16"   aria-pressed="false" >
        				<span class="glyph" aria-label="add">
							<span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
						</span>
						<div>
							<span class="bulky-label"></span>
						</div>
					</button>    
    			</div>
    			<div class="il-standard-popover-content" style="display:none;" id="id_15"></div>
			</div>
 		</div>
        <input class="il-filter-field-status" type="hidden" name="__filter_status_0" value="1" />
        <input class="il-filter-field-status" type="hidden" name="__filter_status_1" value="1" />
    </form>
</div>
EOT;

		$this->assertHTMLEquals($expected, $html);
	}

	public function test_render_deactivated_collapsed() {

		$f = $this->buildFactory();
		$if = $this->buildInputFactory();
		$inputs = [$if->text("Title"), $if->select("Selection", ["One", "Two", "Three"])];
		$inputs_rendered = [true, false];

		$filter = $f->standard("#", "#", "#", "#",
			"#", "#", $inputs, $inputs_rendered, false, false);

		$r = $this->getDefaultRenderer();
		$html = $r->render($filter);

		$expected = <<<EOT
<div class="il-filter disabled   collapsed">
    <form class="il-standard-form form-horizontal" enctype="multipart/formdata" action="" method="post" novalidate="novalidate">
        <div class="il-filter-bar">
            <span class="il-filter-bar-opener"  aria-expanded="false">
                <button class="btn btn-bulky" data-action="#" id="id_1"   aria-pressed="undefined" >
                    <span class="glyph" aria-label="expand_content">
                    <span class="glyphicon glyphicon-triangle-right" aria-hidden="true"></span>
                    </span>
                    <div><span class="bulky-label">filter</span></div>
                </button>
            </span>
            <span class="il-filter-bar-opener-disabled"> filter </span>
            <span class="il-filter-bar-controls">
                <button class="btn btn-bulky ilSubmitInactive disabled" data-action=""   aria-pressed="undefined" >
                    <span class="glyph" aria-label="apply">
                    <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    </span>
                    <div><span class="bulky-label">apply</span></div>
                </button>
                <button class="btn btn-bulky ilSubmitInactive disabled" data-action=""   aria-pressed="undefined" >
                    <span class="glyph" aria-label="reset">
                    <span class="glyphicon glyphicon-repeat" aria-hidden="true"></span>
                    </span>
                    <div><span class="bulky-label">reset</span></div>
                </button>
                <button class="il-toggle-button" id="id_2" aria-pressed="false">
                    <div class="il-toggle-switch"></div>
                </button>
            </span>
        </div>
        <div id="inputs" class="il-filter-input-section form-group row">
            <div class="col-md-4 il-popover-container">
                <div class="input-group">
                    <span class="input-group-addon leftaddon">Title</span>
                    <span role="button" class="form-control il-filter-field" data-placement="bottom"></span>
                    <div class="il-standard-popover-content" style="display:none;" id="id_4"></div>
                    <span class="input-group-addon rightaddon">
                    <a class="glyph disabled" aria-label="remove" aria-disabled="true">
                    <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                    </a>
                    </span>
                </div>
            </div>
            <div class="col-md-4 il-popover-container">
                <div class="input-group">
                    <span class="input-group-addon leftaddon">Selection</span>
                    <span role="button" class="form-control il-filter-field" data-placement="bottom"></span>
                    <div class="il-standard-popover-content" style="display:none;" id="id_6"></div>
                    <span class="input-group-addon rightaddon">
                    <a class="glyph disabled" aria-label="remove" aria-disabled="true">
                    <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                    </a>
                    </span>
                </div>
            </div>
        </div>
        <input class="il-filter-field-status" type="hidden" name="__filter_status_0" value="1" />
        <input class="il-filter-field-status" type="hidden" name="__filter_status_1" value="1" />
    </form>
</div>
EOT;

		$this->assertHTMLEquals($expected, $html);
	}

	public function test_render_activated_expanded() {

		$f = $this->buildFactory();
		$if = $this->buildInputFactory();
		$inputs = [$if->text("Title"), $if->select("Selection", ["One", "Two", "Three"])];
		$inputs_rendered = [true, false];

		$filter = $f->standard("#", "#", "#", "#",
			"#", "#", $inputs, $inputs_rendered, true, true);

		$r = $this->getDefaultRenderer();
		$html = $r->render($filter);

		$expected = <<<EOT
<div class="il-filter  enabled expanded ">
    <form class="il-standard-form form-horizontal" enctype="multipart/formdata" action="" method="post" novalidate="novalidate">
        <div class="il-filter-bar">
            <span class="il-filter-bar-opener"  aria-expanded="false">
                <button class="btn btn-bulky" data-action="" id="id_1"   aria-pressed="undefined" >
                    <span class="glyph" aria-label="collapse_content">
                    <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                    </span>
                    <div><span class="bulky-label">filter</span></div>
                </button>
            </span>
            <span class="il-filter-bar-opener-disabled"> filter </span>
            <span class="il-filter-bar-controls">
                <button class="btn btn-bulky" data-action="" id="id_2"   aria-pressed="undefined" >
                    <span class="glyph" aria-label="apply">
                    <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    </span>
                    <div><span class="bulky-label">apply</span></div>
                </button>
                <button class="btn btn-bulky" data-action="#" id="id_3"   aria-pressed="undefined" >
                    <span class="glyph" aria-label="reset">
                    <span class="glyphicon glyphicon-repeat" aria-hidden="true"></span>
                    </span>
                    <div><span class="bulky-label">reset</span></div>
                </button>
                <button class="il-toggle-button on" id="id_4" aria-pressed="false">
                    <div class="il-toggle-switch"></div>
                </button>
            </span>
        </div>
        <div class="il-filter-inputs-active clearfix"> <span id="1"> </span>  <span id="2"> </span> </div>
        <div id="inputs" class="il-filter-input-section form-group row">
            <div class="col-md-4 il-popover-container">
                <div class="input-group">
                    <span class="input-group-addon leftaddon">Title</span>
                    <span role="button" tabindex="0" class="form-control il-filter-field" id="id_7" data-placement="bottom"></span>
                    <div class="il-standard-popover-content" style="display:none;" id="id_6"></div>
                    <span class="input-group-addon rightaddon">
                    <a class="glyph" aria-label="remove" id="id_8">
                    <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                    </a>
                    </span>
                </div>
            </div>
            <div class="col-md-4 il-popover-container">
                <div class="input-group">
                    <span class="input-group-addon leftaddon">Selection</span>
                    <span role="button" tabindex="0" class="form-control il-filter-field" id="id_11" data-placement="bottom"></span> 
                    <div class="il-standard-popover-content" style="display:none;" id="id_10"></div>
                    <span class="input-group-addon rightaddon"><a class="glyph" aria-label="remove" id="id_12">
                    <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                    </a>
                    </span>
                </div>
            </div>
            <div class="col-md-4 il-popover-container">
                <div class="input-group">
                    <button class="btn btn-bulky" id="id_16"   aria-pressed="false" >
                        <span class="glyph" aria-label="add">
                        <span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span>
                        </span>
                        <div><span class="bulky-label"></span></div>
                    </button>
                </div>
                <div class="il-standard-popover-content" style="display:none;" id="id_15"></div>
            </div>
        </div>
        <input class="il-filter-field-status" type="hidden" name="__filter_status_0" value="1" />
        <input class="il-filter-field-status" type="hidden" name="__filter_status_1" value="1" />
    </form>
</div>
EOT;

		$this->assertHTMLEquals($expected, $html);
	}

	public function test_render_deactivated_expanded() {

		$f = $this->buildFactory();
		$if = $this->buildInputFactory();
		$inputs = [$if->text("Title"), $if->select("Selection", ["One", "Two", "Three"])];
		$inputs_rendered = [true, false];

		$filter = $f->standard("#", "#", "#", "#",
			"#", "#", $inputs, $inputs_rendered, false, true);

		$r = $this->getDefaultRenderer();
		$html = $r->render($filter);

		$expected = <<<EOT
<div class="il-filter disabled  expanded ">
    <form class="il-standard-form form-horizontal" enctype="multipart/formdata" action="" method="post" novalidate="novalidate">
        <div class="il-filter-bar">
            <span class="il-filter-bar-opener"  aria-expanded="false">
                <button class="btn btn-bulky" data-action="#" id="id_1"   aria-pressed="undefined" >
                    <span class="glyph" aria-label="collapse_content">
                    <span class="glyphicon glyphicon-triangle-bottom" aria-hidden="true"></span>
                    </span>
                    <div><span class="bulky-label">filter</span></div>
                </button>
            </span>
            <span class="il-filter-bar-opener-disabled"> filter </span>
            <span class="il-filter-bar-controls">
                <button class="btn btn-bulky ilSubmitInactive disabled" data-action=""   aria-pressed="undefined" >
                    <span class="glyph" aria-label="apply">
                    <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>
                    </span>
                    <div><span class="bulky-label">apply</span></div>
                </button>
                <button class="btn btn-bulky ilSubmitInactive disabled" data-action=""   aria-pressed="undefined" >
                    <span class="glyph" aria-label="reset">
                    <span class="glyphicon glyphicon-repeat" aria-hidden="true"></span>
                    </span>
                    <div><span class="bulky-label">reset</span></div>
                </button>
                <button class="il-toggle-button" id="id_2" aria-pressed="false">
                    <div class="il-toggle-switch"></div>
                </button>
            </span>
        </div>
        <div id="inputs" class="il-filter-input-section form-group row">
            <div class="col-md-4 il-popover-container">
                <div class="input-group">
                    <span class="input-group-addon leftaddon">Title</span>
                    <span role="button"  class="form-control il-filter-field"  data-placement="bottom"></span> 
                    <div class="il-standard-popover-content" style="display:none;" id="id_4"></div>
                    <span class="input-group-addon rightaddon"><a class="glyph disabled" aria-label="remove" aria-disabled="true">
                    <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                    </a>
                    </span>
                </div>
            </div>
            <div class="col-md-4 il-popover-container">
                <div class="input-group">
                    <span class="input-group-addon leftaddon">Selection</span>
                    <span role="button"  class="form-control il-filter-field"  data-placement="bottom"></span> 
                    <div class="il-standard-popover-content" style="display:none;" id="id_6"></div>
                    <span class="input-group-addon rightaddon"><a class="glyph disabled" aria-label="remove" aria-disabled="true">
                    <span class="glyphicon glyphicon-minus-sign" aria-hidden="true"></span>
                    </a>
                    </span>
                </div>
            </div>
        </div>
        <input class="il-filter-field-status" type="hidden" name="__filter_status_0" value="1" />
        <input class="il-filter-field-status" type="hidden" name="__filter_status_1" value="1" />
    </form>
</div>
EOT;

		$this->assertHTMLEquals($expected, $html);
	}
}
