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
		$data_f = new ILIAS\Data\Factory();

		$back = $data_f->link("", "http://www.ilias.de");
		$next = $data_f->link("", "http://www.github.com");
		$button = $button_f->standard("Today", "");

		$this->assertInstanceOf("ILIAS\\Data\\Link\\Link", $back);
		$this->assertInstanceOf("ILIAS\\Data\\Link\\Link", $next);
		$this->assertInstanceOf("ILIAS\\UI\\Component\\Button\\Button", $button);
		$this->assertInstanceOf("ILIAS\\UI\\Component\\ViewControl\\Factory", $view_control_f);

		$section = $view_control_f->section($back,$button,$next);
		$this->assertInstanceOf( "ILIAS\\UI\\Component\\ViewControl\\Section", $section);
	}


	public function test_viewcontrol_section_get_previous_actions()
	{
		$data_f = new ILIAS\Data\Factory();
		$button_f = new ILIAS\UI\Implementation\Component\Button\Factory();


		$back = $data_f->link("", "http://www.ilias.de");
		$next = $data_f->link("", "http://www.github.com");
		$button = $button_f->standard("Today", "");

		$action = $this->getViewControlFactory()->section($back,$button,$next)->getPreviousActions();

		$this->assertInstanceOf("\\ILIAS\\Data\\Link\\Link",$action);
	}

	public function test_viewcontrol_section_get_next_actions()
	{
		$data_f = new ILIAS\Data\Factory();
		$button_f = new ILIAS\UI\Implementation\Component\Button\Factory();

		$back = $data_f->link("", "http://www.ilias.de");
		$next = $data_f->link("", "http://www.github.com");
		$button = $button_f->standard("Today", "");

		$action = $this->getViewControlFactory()->section($back,$button,$next)->getNextActions();

		$this->assertInstanceOf("\\ILIAS\\Data\\Link\\Link",$action);
	}

	//TODO test render

}