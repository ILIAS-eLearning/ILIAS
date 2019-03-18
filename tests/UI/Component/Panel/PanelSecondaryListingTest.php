<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;


/**
 * Test secondary listing panels
 */
class PanelSecodaryListingTest extends ILIAS_UI_TestBase {

	/**
	 * @return \ILIAS\UI\Implementation\Component\Panel\Secondary\Factory
	 */
	public function getFactory() {
		return new I\Component\Panel\Secondary\Factory();
	}

	public function test_implements_factory_interface() {
		$f = $this->getFactory();

		$secondary_panel = $f->listing("List Title", array(
			new I\Component\Item\Group("Subtitle 1", array(
				new I\Component\Item\Standard("title1"),
				new I\Component\Item\Standard("title2")
			)),
			new I\Component\Item\Group("Subtitle 2", array(
				new I\Component\Item\Standard("title3")
			))
		));

		$this->assertInstanceOf( "ILIAS\\UI\\Component\\Panel\\Secondary\\Listing", $secondary_panel);
	}

	public function test_get_title_get_groups() {
		$f = $this->getFactory();

		$groups = array(
			new I\Component\Item\Group("Subtitle 1", array(
				new I\Component\Item\Standard("title1"),
				new I\Component\Item\Standard("title2")
			)),
			new I\Component\Item\Group("Subtitle 2", array(
				new I\Component\Item\Standard("title3")
			))
		);

		$c = $f->standard("title", $groups);

		$this->assertEquals($c->getTitle(), "title");
		$this->assertEquals($c->getItemGroups(), $groups);
	}

	public function test_with_actions() {
		$f = $this->getFactory();

		$actions = new I\Component\Dropdown\Standard(array(
			new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
			new I\Component\Button\Shy("GitHub", "https://www.github.com")
		));

		$groups = array();

		$c = $f->standard("title", $groups)
			->withActions($actions);

		$this->assertEquals($c->getActions(), $actions);
	}
}
