<?php

use ILIAS\GlobalScreen\Collector\MainMenu\Information\TypeInformation;
use ILIAS\GlobalScreen\Collector\MainMenu\Information\TypeInformationCollection;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\MainMenu\Item\Complex;
use ILIAS\GlobalScreen\MainMenu\Item\Link;
use ILIAS\GlobalScreen\MainMenu\Item\LinkList;
use ILIAS\GlobalScreen\MainMenu\Item\Lost;
use ILIAS\GlobalScreen\MainMenu\Item\RepositoryLink;
use ILIAS\GlobalScreen\MainMenu\Item\Separator;
use ILIAS\GlobalScreen\MainMenu\TopItem\TopLinkItem;
use ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem;
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
	 * @return TopParentItem[]
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
	public function provideTypeInformation(): TypeInformationCollection {
		$c = new TypeInformationCollection();
		$c->add(new TypeInformation(TopParentItem::class, $this->translateType(TopParentItem::class), new ilMMTopParentItemRenderer()));
		$c->add(new TypeInformation(TopLinkItem::class, $this->translateType(TopLinkItem::class), new ilMMTopLinkItemRenderer(), new ilMMTypeHandlerTopLink()));
		$c->add(new TypeInformation(Link::class, $this->translateType(Link::class), null, new ilMMTypeHandlerLink()));
		$link_list = new TypeInformation(LinkList::class, $this->translateType(LinkList::class));
		$link_list->setCreationPrevented(true);
		$c->add($link_list);
		$c->add(new TypeInformation(Separator::class, $this->translateType(Separator::class), null, new ilMMTypeHandlerSeparator()));
		$c->add(new TypeInformation(RepositoryLink::class, $this->translateType(RepositoryLink::class), null, new ilMMTypeHandlerRepositoryLink()));
		$complex = new TypeInformation(Complex::class, $this->translateType(Complex::class));
		$complex->setCreationPrevented(true);
		$c->add($complex);
		$lost = new TypeInformation(Lost::class, $this->translateType(Lost::class), new ilMMLostItemRenderer());
		$lost->setCreationPrevented(true);
		$c->add($lost);

		return $c;
	}


	/**
	 * @param string $type
	 *
	 * @return string
	 */
	private function translateType(string $type): string {
		$last_part = substr(strrchr($type, "\\"), 1);
		$last_part = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $last_part));

		return $this->dic->language()->txt("type_" . strtolower($last_part));
	}
}
