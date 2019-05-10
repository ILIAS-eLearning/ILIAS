<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasIcon;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Class BaseTypeRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BaseTypeRenderer implements TypeRenderer {

	/**
	 * @var Factory
	 */
	protected $ui_factory;


	/**
	 * BaseTypeRenderer constructor.
	 */
	public function __construct() {
		global $DIC;
		$this->ui_factory = $DIC->ui()->factory();
	}


	/**
	 * @inheritDoc
	 */
	public function getComponentForItem(isItem $item): Component {
		return $this->ui_factory->legacy("");
	}


	/**
	 * @return \ILIAS\UI\Component\Symbol\Icon\Standard
	 */
	protected function getStandardIcon(isItem $item): \ILIAS\UI\Component\Symbol\Icon\Icon {
		if ($item instanceof hasIcon && $item->hasIcon()) {
			return $item->getIcon();
		}

		return $this->ui_factory->symbol()->icon()->custom("./src/UI/examples/Layout/Page/Standard/question.svg", 'ILIAS', 'small', true);
	}
}
