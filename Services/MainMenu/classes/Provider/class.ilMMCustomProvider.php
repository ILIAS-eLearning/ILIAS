<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Provider\StaticProvider\AbstractStaticMainMenuProvider;
use ILIAS\GlobalScreen\Provider\StaticProvider\StaticMainMenuProvider;

/**
 * Class ilMMCustomProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMCustomProvider extends AbstractStaticMainMenuProvider implements StaticMainMenuProvider {

	/**
	 * @var \ILIAS\DI\Container
	 */
	protected $dic;


	/**
	 * @return \ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem[]
	 */
	public function getStaticTopItems(): array {
		/**
		 * @var $item ilMMCustomItemStorage
		 */
		$top_items = [];
		foreach (ilMMCustomItemStorage::where(['top_item' => true])->get() as $item) {
			$top_items[] = $this->getSingleCustomItem($item, false);
		}

		return $top_items;
	}


	/**
	 * @return \ILIAS\GlobalScreen\MainMenu\isItem[]
	 */
	public function getStaticSubItems(): array {
		return [];
	}


	/**
	 * @param ilMMCustomItemStorage $storage
	 * @param bool                  $register
	 *
	 * @return \ILIAS\GlobalScreen\MainMenu\isItem
	 */
	public function getSingleCustomItem(ilMMCustomItemStorage $storage, $register = false): \ILIAS\GlobalScreen\MainMenu\isItem {
		$identification = $this->globalScreen()->identification()->core($this)->identifier($storage->getIdentifier());
		if ($register) {
			ilGSIdentificationStorage::registerIdentification($identification, $this);
		}
		switch ($storage->getType()) {
			case \ILIAS\GlobalScreen\MainMenu\TopItem\TopLinkItem::class:
				$item = $this->mainmenu->topLinkItem($identification)->withTitle($storage->getDefaultTitle())->withAction($storage->getAction());
				break;
			case \ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem::class:
			default:
				$item = $this->mainmenu->topParentItem($identification)->withTitle($storage->getDefaultTitle());
				break;
		}
		if ($register) {
			ilMMItemStorage::register($item);
		}

		return $item;
	}
}
