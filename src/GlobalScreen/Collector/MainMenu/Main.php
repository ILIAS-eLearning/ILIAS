<?php namespace ILIAS\GlobalScreen\Collector\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\MainMenu\hasTitle;
use ILIAS\GlobalScreen\MainMenu\isChild;
use ILIAS\GlobalScreen\MainMenu\isItem;
use ILIAS\GlobalScreen\MainMenu\isParent;
use ILIAS\GlobalScreen\MainMenu\isTopItem;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\UI\Implementation\Component\ViewControl\Sortation;

/**
 * Class Main
 *
 * This Collector will collect and then provide all available slates from the
 * providers in the whole system, stack them and enrich them will their content
 * based on the configuration in "Administration".
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Main {

	/**
	 * @var bool
	 */
	private static $constructed = false;
	/**
	 * @var array|isItem[]
	 */
	private static $items = [];
	/**
	 * @var array|isItem[]
	 */
	private static $topitems = [];
	/**
	 * @var ItemInformation|null
	 */
	private $information;
	/**
	 * @var array|Provider[]
	 */
	protected $providers;
	/**
	 * @var bool
	 */
	private $loaded = false;


	/**
	 * Main constructor.
	 *
	 * @param array                $providers
	 * @param ItemInformation|null $information
	 *
	 * @throws \Throwable
	 */
	public function __construct(array $providers, ItemInformation $information = null) {
		if (self::$constructed === true) {
			throw new \LogicException("only one Instance of Main Collector is possible");
		}
		$this->information = $information;
		$this->providers = $providers;
		self::$constructed = true;
		$this->load();
	}


	/**
	 * This will return all available topitems, stacked based on the configuration
	 * in "Administration" and for the visibility of the currently user.
	 * Additionally this will filter sequent Separators to avoid double Separators
	 * in the UI.
	 *
	 * @return isTopItem[]
	 * @throws \Throwable
	 */
	public function getStackedTopItemsForPresentation(): array {
		return $this->getStackedTopItems();
	}


	/**
	 * @return isTopItem[]
	 * @throws \Throwable
	 */
	private function getStackedTopItems(): array {
		$this->load();
		$top_items = [];
		foreach (self::$topitems as $item) {
			$is_visible1 = $item->isVisible();
			$is_item_active1 = $this->information->isItemActive($item);
			$is_always_available1 = $item->isAlwaysAvailable();
			if ((!$is_visible1 || !$is_item_active1 && !$is_always_available1)) {
				continue;
			}
			if ($item instanceof hasTitle && $this->information) {
				$item = $this->information->translateItemForUser($item);
			}
			if ($item instanceof isTopItem && $this->information) {
				if ($item instanceof isParent) {
					$children = [];
					/**
					 * @var $item isParent
					 */
					foreach ($item->getChildren() as $child) {
						$is_visible = $child->isVisible();
						$is_item_active = $this->information->isItemActive($child);
						$is_always_available = $child->isAlwaysAvailable();
						if ((!$is_visible || !$is_item_active && !$is_always_available)) {
							continue;
						}
						$position_of_sub_item = $this->information->getPositionOfSubItem($child);
						if (isset($children[$position_of_sub_item])) {
							$position_of_sub_item = count($children) + 1;
						}
						$children[$position_of_sub_item] = $child;
					}
					ksort($children);
					$item = $item->withChildren($children);
				}
				$position_of_top_item = $this->information->getPositionOfTopItem($item);
				if (isset($top_items[$position_of_top_item])) {
					$position_of_top_item = count($top_items) + 1;
				}
				$top_items[$position_of_top_item] = $item;
			}
		}
		ksort($top_items);

		return $top_items;
	}


	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getSubItems(): array {
		$this->load();
		$sub_items = [];
		foreach (self::$items as $item) {
			if ($item instanceof hasTitle && $this->information) {
				$item = $this->information->translateItemForUser($item);
			}
			if ($item instanceof isChild && $this->information) {
				$sub_items[$this->information->getPositionOfSubItem($item)] = $item;
			}
		}

		return $sub_items;
	}


	/**
	 * @param IdentificationInterface $identification
	 *
	 * @return isItem
	 * @throws \Throwable
	 */
	public function getSingleItem(IdentificationInterface $identification, $force_load = false): isItem {
		if ($force_load) {
			$this->loaded = false;
		}
		$this->load();
		try {
			return self::$items[$identification->serialize()];
		} catch (\Throwable $e) {
			throw $e;
		}
	}


	public function addItemToMap() {

	}


	/**
	 * @return bool
	 * @throws \Throwable
	 */
	private function load(): bool {
		if ($this->loaded === false || $this->loaded === null) {
			/**
			 * @var $provider            \ILIAS\GlobalScreen\Provider\StaticProvider\StaticMainMenuProvider
			 * @var $top_item            \ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem
			 * @var $sub_item            \ILIAS\GlobalScreen\MainMenu\isChild
			 */
			try {
				foreach ($this->providers as $provider) {
					foreach ($provider->getStaticTopItems() as $top_item) {
						self::$topitems[$top_item->getProviderIdentification()->serialize()] = $top_item;
						self::$items[$top_item->getProviderIdentification()->serialize()] = $top_item;
					}
				}
				foreach ($this->providers as $provider) {
					foreach ($provider->getStaticSubItems() as $sub_item) {
						if ($sub_item instanceof isChild && $sub_item->hasParent()) {
							$sub_item->overrideParent($this->information->getParent($sub_item));
							self::$items[$sub_item->getProviderIdentification()->serialize()] = $sub_item;
							if (isset(self::$topitems[$sub_item->getParent()->serialize()]) && self::$topitems[$sub_item->getParent()->serialize()] instanceof isParent) {
								self::$topitems[$sub_item->getParent()->serialize()]->appendChild($sub_item);
							}
						}
					}
				}
				$this->loaded = true;
			} catch (\Throwable $e) {
				throw $e;
			}
		}

		return $this->loaded;
	}
}
