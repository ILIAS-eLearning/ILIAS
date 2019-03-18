<?php

/* Copyright (c) 2019 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

require_once(__DIR__."/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__."/../../Base.php");

use \ILIAS\UI\Component as C;
use \ILIAS\UI\Implementation as I;


/**
 * Test secondary legacy panels
 */
class PanelSecodaryLegacyTest extends ILIAS_UI_TestBase {

	/**
	 * @return \ILIAS\UI\Implementation\Component\Panel\Secondary\Factory
	 */
	public function getFactory() {
		return new I\Component\Panel\Secondary\Factory();
	}

	public function getLegacy() {
		return new I\Component\Legacy\Legacy("legacy content");
	}

	public function test_implements_factory_interface() {
		$f = $this->getFactory();

		$legacy =  $this->getLegacy();

		$secondary_panel = $f->legacy("List Title", $legacy);

		$this->assertInstanceOf( "ILIAS\\UI\\Component\\Panel\\Secondary\\Legacy", $secondary_panel);
	}


	public function test_get_title() {
		$f = $this->getFactory();

		$legacy =  $this->getLegacy();

		$secondary_panel = $f->legacy("title", $legacy);

		$this->assertEquals($secondary_panel->getTitle(), "title");
	}

	public function test_get_legacy() {
		$f = $this->getFactory();

		$legacy =  $this->getLegacy();

		$secondary_panel = $f->legacy("title", $legacy);

		$this->assertEquals($secondary_panel->getLegacyComponent(), $legacy);
	}

	public function test_with_actions() {
		$f = $this->getFactory();

		$legacy =  $this->getLegacy();

		$actions = new I\Component\Dropdown\Standard(array(
			new I\Component\Button\Shy("ILIAS", "https://www.ilias.de"),
			new I\Component\Button\Shy("GitHub", "https://www.github.com")
		));

		$secondary_panel = $f->legacy("title", $legacy)
			->withActions($actions);

		$this->assertEquals($secondary_panel->getActions(), $actions);
	}
}
