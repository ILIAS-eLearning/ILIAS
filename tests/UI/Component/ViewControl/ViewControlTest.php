<?php

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;

class ViewControlTest extends ILIAS_UI_TestBase
{
	public function getViewControlFactory()
	{
		return new \ILIAS\UI\Implementation\Component\ViewControl\Factory();
	}

	public function test_implements_factory_interface()
	{
		$view_control_f = $this->getViewControlFactory();
		$button_f = new ILIAS\UI\Implementation\Component\Button\Factory();

		$back = $button_f->standard("", "http://www.ilias.de");
		$next = $button_f->standard("", "http://www.github.com");
		$button = $button_f->standard("Today", "");

		$this->assertInstanceOf("ILIAS\\UI\\Component\\Button\\Button", $back);
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Button\\Button", $next);
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Button\\Button", $button);
		$this->assertInstanceOf("ILIAS\\UI\\Component\\ViewControl\\Factory", $view_control_f);

		$section = $view_control_f->section($back,$button,$next);
		$this->assertInstanceOf( "ILIAS\\UI\\Component\\ViewControl\\Section", $section);
	}


	public function test_viewcontrol_section_get_previous_actions()
	{
		$button_f = new ILIAS\UI\Implementation\Component\Button\Factory();

		$back = $button_f->standard("", "http://www.ilias.de");
		$next = $button_f->standard("", "http://www.github.com");
		$button = $button_f->standard("Today", "");

		$action = $this->getViewControlFactory()->section($back,$button,$next)->getPreviousActions();

		$this->assertInstanceOf("ILIAS\\UI\\Component\\Button\\Button",$action);
	}

	public function test_viewcontrol_section_get_next_actions()
	{
		$button_f = new ILIAS\UI\Implementation\Component\Button\Factory();

		$back = $button_f->standard("", "http://www.ilias.de");
		$next = $button_f->standard("", "http://www.github.com");
		$button = $button_f->standard("Today", "");

		$action = $this->getViewControlFactory()->section($back,$button,$next)->getNextActions();

		$this->assertInstanceOf("ILIAS\\UI\\Component\\Button\\Button",$action);
	}

	public function test_render_viewcontrol_section()
	{
		$view_control_f = $this->getViewControlFactory();
		$button_f = new ILIAS\UI\Implementation\Component\Button\Factory();

		$r = $this->getDefaultRenderer();

		$back = $button_f->standard("", "http://www.ilias.de");
		$next = $button_f->standard("", "http://www.github.com");
		$button = $button_f->standard("Today", "");

		$section = $view_control_f->section($back,$button,$next);

		$html = $r->render($section);
		$this->assertContains("glyphicon-chevron-left", $html);
		$this->assertContains("glyphicon-chevron-right", $html);
		$this->assertContains("il-viewcontrol-section", $html);
		$this->assertContains('back', $html);
		$this->assertContains('next', $html);
		$this->assertContains("btn",$html);

		$expected = $this->getSectionExpectedHTML();
		$this->assertHTMLEquals($expected,$html);

	}

	public function getUIFactory()
	{
		return new \ILIAS\UI\Implementation\Factory();
	}


	protected function getSectionExpectedHTML()
	{
		$expected = <<<EOT
<div class="il-viewcontrol-section">
<a class="glyph" href="http://www.ilias.de" aria-label="back">
<span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
</a>
<a class="btn btn-default" href="" data-action="">Today</a><a class="glyph" href="http://www.github.com" aria-label="next">
<span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
</a>
</div>
EOT;
		return $expected;
	}

}