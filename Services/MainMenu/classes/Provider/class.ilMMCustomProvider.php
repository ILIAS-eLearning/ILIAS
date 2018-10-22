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
			$top_items[] = $this->getSingleCustomItem($item, true);
		}

		return $top_items;
	}


	/**
	 * @return \ILIAS\GlobalScreen\MainMenu\isItem[]
	 */
	public function getStaticSubItems(): array {
		/**
		 * @var $item ilMMCustomItemStorage
		 */
		$items = [];
		foreach (ilMMCustomItemStorage::where(['top_item' => false])->get() as $item) {
			$items[] = $this->getSingleCustomItem($item, true);
		}

		return $items;
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

		$item = $this->globalScreen()->mainmenu()->custom($storage->getType(), $identification);

		if ($item instanceof \ILIAS\GlobalScreen\MainMenu\hasTitle) {
			$item = $item->withTitle($storage->getDefaultTitle());
		}
		if ($item instanceof \ILIAS\GlobalScreen\MainMenu\hasAction) {
			$item = $item->withAction("#");
		}
		if ($item instanceof \ILIAS\GlobalScreen\MainMenu\isChild) {
			$mm_item = ilMMItemStorage::find($identification->serialize());
			$parent_identification = "";
			if ($mm_item instanceof ilMMItemStorage) {
				$parent_identification = $mm_item->getParentIdentification();
			}

			if ($parent_identification) {
				$item = $item->withParent(
					$this->globalScreen()
						->identification()
						->fromSerializedIdentification($parent_identification)
				);
			}
		}

		if ($register) {
			ilMMItemStorage::register($item);
		}

		return $item;
	}


	/**
	 * @inheritDoc
	 */
	public function provideTypeHandlers(): array {
		return [new ilMMTypeHandlerLink(), new ilMMTypeHandlerTopLink()];
	}
}
