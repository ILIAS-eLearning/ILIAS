<?php
require_once(__DIR__ . "/../../../../libs/composer/vendor/autoload.php");
require_once(__DIR__ . "/../../Base.php");

use \ILIAS\UI\Component as C;

/**
 * Base class for modal tests
 *
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
abstract class ModalBase extends ILIAS_UI_TestBase {

	public function getUIFactory() {
		return new \ILIAS\UI\Implementation\Factory();
	}

	protected function getModalFactory() {
		return new \ILIAS\UI\Implementation\Component\Modal\Factory(new SignalGeneratorMock());
	}

	protected function getButtonFactory() {
		return new \ILIAS\UI\Implementation\Component\Button\Factory();
	}

	protected function getDummyComponent() {
		return new DummyComponent();
	}

	public function normalizeHTML($html) {
		$html = parent::normalizeHTML($html);
		// The times entity is used for closing the modal and not supported in DomDocument::loadXML()
		return str_replace(['&times;', "\t"], ['', ''], $html);
	}
}
