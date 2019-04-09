<?php

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;
use \ILIAS\UI\Implementation\Component\SignalGenerator;

class ViewControlTest extends ILIAS_UI_TestBase {

	protected $actions = array (
		"ILIAS" => "http://www.ilias.de",
		"Github" => "http://www.github.com"
	);

	protected $aria_label = "Mode View Controler";
	protected $role = "group";
	protected $active = "Github";

	public function getViewControlFactory()
	{
		return new I\Component\ViewControl\Factory(new SignalGenerator());
	}

	public function test_implements_factory_interface()
	{
		$view_control_f = $this->getViewControlFactory();
		$button_f = new I\Component\Button\Factory();

		$back = new I\Component\Button\Standard("", "http://www.ilias.de");
		$next = new I\Component\Button\Standard("", "http://www.github.com");
		$button = new I\Component\Button\Standard("Today", "");

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
		$this->assertStringContainsString("glyphicon-chevron-left", $html);
		$this->assertStringContainsString("glyphicon-chevron-right", $html);
		$this->assertStringContainsString("il-viewcontrol-section", $html);
		$this->assertStringContainsString("btn",$html);

		$expected = $this->getSectionExpectedHTML();
		$this->assertHTMLEquals($expected,$html);

		$f = $this->getViewControlFactory();
		$this->assertInstanceOf("ILIAS\\UI\\Component\\ViewControl\\Factory", $f);
	}

	public function test_viewcontrol_with_active()
	{
		$f = $this->getViewControlFactory();

		$this->assertEquals($this->active, $f->mode($this->actions, $this->aria_label)->withActive($this->active)->getActive());
		$this->assertNotEquals($this->active, $f->mode($this->actions, $this->aria_label)->withActive("Dummy text")->getActive());
	}

	public function test_viewcontrol_get_actions()
	{
		$f = $this->getViewControlFactory();
		$r = $this->getDefaultRenderer();

		$this->assertIsArray($f->mode($this->actions, $this->aria_label)->getLabelledActions());
	}

	public function test_render_viewcontrol_mode()
	{
		$f = $this->getViewControlFactory();
		$r = $this->getDefaultRenderer();
		$mode = $f->mode($this->actions, $this->aria_label);

		$html = $this->normalizeHTML($r->render($mode));

		$active = $mode->getActive();
		if($active == "") {
			$activate_first_item = true;
		}

		$expected = "<div class=\"btn-group il-viewcontrol-mode\" aria-label=\"".$this->aria_label."\" role=\"".$this->role."\">";
		foreach ($this->actions as $label => $action)
		{

			if($activate_first_item) {
				$expected .= "<button class=\"btn btn-default ilSubmitInactive disabled engaged\" aria-label=\"$label\" aria-pressed=\"true\" data-action=\"$action\">$label</button>";
				$activate_first_item = false;
			} else if($active == $label) {
				$expected .= "<button class=\"btn btn-default ilSubmitInactive disabled engaged\" aria-label=\"$label\" aria-pressed=\"true\" data-action=\"$action\">$label</button>";
			}
			else {
				$expected .= "<button class=\"btn btn-default\" aria-label=\"$label\" aria-pressed=\"false\" data-action=\"$action\" id=\"id_1\">$label</button>";
			}
		}
		$expected .= "</div>";

		$this->assertHTMLEquals($expected, $html);
	}

	public function getUIFactory() {
		$factory = new class extends NoUIFactory {
			public function counter() {
				return new I\Component\Counter\Factory();
			}
			public function button() {
				return new I\Component\Button\Factory();
			}
		};
		return $factory;
	}

	protected function getSectionExpectedHTML()
	{
		$expected = <<<EOT
<div class="il-viewcontrol-section">
<a class="btn btn-default " type="button" href="http://www.ilias.de" data-action="http://www.ilias.de"><span class="glyphicon glyphicon-chevron-left"></span></a>
<button class="btn btn-default" data-action="">Today</button>
<a class="btn btn-default " type="button" href="http://www.github.com" data-action="http://www.github.com"><span class="glyphicon glyphicon-chevron-right"></span></a>
</div>
EOT;
		return $expected;
	}
}
