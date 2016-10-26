<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;

class ComponentDummy implements C\Component {
	public function __construct($id = ""){
		$this->id = $id;
	}
}

/**
 * Test on button implementation.
 */
class PanelTest extends ILIAS_UI_TestBase {

	/**
	 * @return \ILIAS\UI\Implementation\Component\Panel\Factory
	 */
	public function getPanelFactory() {
		return new \ILIAS\UI\Implementation\Component\Panel\Factory();
	}

	/**
	 * @return \ILIAS\UI\Implementation\Factory
	 */
	public function getFactory() {
		return new \ILIAS\UI\Implementation\Factory();
	}

	public function test_implements_factory_interface() {
		$f = $this->getPanelFactory();

		$this->assertInstanceOf("ILIAS\\UI\\Component\\Panel\\Factory", $f);
		$this->assertInstanceOf
		( "ILIAS\\UI\\Component\\Panel\\Standard"
				, $f->standard("Title",array(new ComponentDummy()))
		);
		$this->assertInstanceOf
		( "ILIAS\\UI\\Component\\Panel\\Sub"
				, $f->sub("Title",array(new ComponentDummy()))
		);
		$this->assertInstanceOf
		( "ILIAS\\UI\\Component\\Panel\\Report"
				, $f->report("Title",$f->sub("Title",array(new ComponentDummy())))
		);
	}

	public function test_standard_get_title() {
		$f = $this->getPanelFactory();
		$p = $f->standard("Title",array(new ComponentDummy()));

		$this->assertEquals($p->getTitle(), "Title");
	}

	public function test_standard_get_content() {
		$f = $this->getPanelFactory();
		$c =  new ComponentDummy();
		$p = $f->standard("Title",array($c));

		$this->assertEquals($p->getContent(), array($c));
	}


	public function test_sub_with_card() {
		$fp = $this->getPanelFactory();
		$f = $this->getFactory();

		$p = $fp->sub("Title",array(new ComponentDummy()));

		$card = $f->card("Card Title");

		$p = $p->withCard($card);

		$this->assertEquals($p->getCard(), $card);
	}

	public function test_report_get_title() {
		$f = $this->getPanelFactory();
		$sub = $f->sub("Title",array(new ComponentDummy()));
		$p = $f->report("Title",array($sub));

		$this->assertEquals($p->getTitle(), "Title");
	}

	public function test_report_get_content() {
		$f = $this->getPanelFactory();
		$sub = $f->sub("Title",array(new ComponentDummy()));
		$p = $f->report("Title",$sub);

		$this->assertEquals($p->getContent(), array($sub));
	}


	public function test_render_standard() {
		$f = $this->getPanelFactory();
		$r = $this->getDefaultRenderer();
		$p = $f->standard("Title",array());

		$html = new DOMDocument();
		$html->formatOutput = true;
		$html->preserveWhiteSpace = false;

		$expected = new DOMDocument();
		$expected->formatOutput = true;
		$expected->preserveWhiteSpace = false;

		$html = $r->render($p);

		$expected_html =
				"<div class=\"panel panel-primary\">".
				"   <div class=\"panel-heading ilHeader\">".
				"       <h3 class=\"ilHeader\">Title</h3>".
				"   </div>".
				"   <div class=\"panel-body\"></div>".
				"</div>";

		$this->assertHTMLEquals($expected_html, $html);
	}

	public function test_render_sub() {
		$f = $this->getFactory();
		$fp = $this->getPanelFactory();
		$r = $this->getDefaultRenderer();
		$p = $fp->sub("Title",array());
		$card = $f->card("Card Title");
		$p = $p->withCard($card);
		$html = $r->render($p);

		$expected_html =
				"<div class=\"panel panel-default\">".
				"   <div class=\"panel-heading ilBlockHeader\">".
				"       <h3 class=\"ilBlockHeader\">Title</h3>".
				"   </div>".
				"   <div class=\"panel-body\"><div class=\"row\">".
				"       <div class=\"col-sm-8\"></div>".
				"       <div class=\"col-sm-4\">".
				"           <div class=\"il-card thumbnail\"><div class=\"caption\"><h2 class=\"card-title\">Card Title</h2></div></div>".
				"       </div>".
				"   </div></div>".
				"</div>";

		$this->assertHTMLEquals($expected_html, $html);
	}
	public function test_render_report() {
		$f = $this->getFactory();
		$fp = $this->getPanelFactory();
		$r = $this->getDefaultRenderer();
		$sub = $fp->sub("Title",array());
		$card = $f->card("Card Title");
		$sub = $sub->withCard($card);
		$report = $fp->report("Title",$sub);

		$html = $r->render($report);

		$expected_html =
				"<div class=\"panel panel-primary\">".
				"   <div class=\"panel-heading ilHeader\">".
				"       <h3 class=\"ilHeader\">Title</h3>".
				"   </div>".
				"   <div class=\"panel-body\">".
				"       <div class=\"panel panel-default\">".
				"           <div class=\"panel-heading ilBlockHeader\">".
				"               <h3 class=\"ilBlockHeader\">Title</h3>".
				"           </div>".
				"           <div class=\"panel-body\"><div class=\"row\">".
				"               <div class=\"col-sm-8\"></div>".
				"               <div class=\"col-sm-4\">".
				"                   <div class=\"il-card thumbnail\"><div class=\"caption\"><h2 class=\"card-title\">Card Title</h2></div></div>".
				"               </div>".
				"           </div></div>".
				"       </div>".
				"   </div>".
				"</div>";

		$this->assertHTMLEquals($expected_html, $html);
	}
}
