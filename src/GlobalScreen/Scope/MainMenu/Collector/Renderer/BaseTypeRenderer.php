<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasIcon;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Tool\Factory\Tool;
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
		if ($item instanceof Tool) {
			$symbol = $this->getStandardIcon($item);

			return $this->ui_factory->mainControls()->slate()->legacy($item->getTitle(), $symbol, $item->getContent());
			// return $item->getContent();
		}

		return $this->ui_factory->legacy("");
	}


	/**
	 * @return \ILIAS\UI\Component\Icon\Standard
	 */
	protected function getStandardIcon(isItem $item): \ILIAS\UI\Component\Icon\Icon {
		if ($item instanceof hasIcon && $item->hasIcon()) {
			return $item->getIcon();
		}

		return $this->ui_factory->icon()->custom("./src/UI/examples/Layout/Page/Standard/question.svg", 'ILIAS', 'small', true);
	}
}
