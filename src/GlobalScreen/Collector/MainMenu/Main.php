<?php namespace ILIAS\GlobalScreen\Collector\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\MainMenu\hasTitle;
use ILIAS\GlobalScreen\MainMenu\isChild;
use ILIAS\GlobalScreen\MainMenu\isItem;
use ILIAS\GlobalScreen\MainMenu\isParent;
use ILIAS\GlobalScreen\MainMenu\isTopItem;
use ILIAS\GlobalScreen\Provider\Provider;

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
			if (!$is_visible1 || (!$is_item_active1 && !$is_always_available1)) {
				continue;
			}
			if ($item instanceof isTopItem && $this->information) {
				if ($item instanceof isParent) {
					$children = [];
					/**
					 * @var $item  isParent
					 * @var $child isChild
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
							$position_of_sub_item = max(array_keys($children)) + 1;
						}
						$children[$position_of_sub_item] = $child;
					}
					ksort($children);
					$item = $item->withChildren($children);
				}
				$position_of_top_item = $this->information->getPositionOfTopItem($item);
				if (isset($top_items[$position_of_top_item])) {
					$position_of_top_item = max(array_keys($top_items)) + 1;
				}
				$top_items[$position_of_top_item] = $item;
			}
		}
		ksort($top_items);

		return $top_items;
	}


	/**
	 * @param IdentificationInterface $identification
	 *
	 * @return isItem
	 * @throws \Throwable
	 */
	public function getSingleItem(IdentificationInterface $identification): isItem {
		$this->load();
		try {
			return self::$items[$identification->serialize()];
		} catch (\Throwable $e) {
			global $DIC;

			return $DIC->globalScreen()->mainmenu()->topParentItem(new NullIdentification($identification))
				->withTitle($DIC->language()->txt("deleted_item"))
				->withAlwaysAvailable(true)
				->withVisibilityCallable(
					function () use ($DIC) {
						return (bool)($DIC->rbac()->system()->checkAccess("visible", SYSTEM_FOLDER_ID));
					}
				);
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
				$this->loaded = true;
				foreach ($this->providers as $provider) {
					foreach ($provider->getStaticTopItems() as $top_item) {
						if ($top_item instanceof hasTitle && $this->information) {
							$top_item = $this->information->translateItemForUser($top_item);
						}
						self::$topitems[$top_item->getProviderIdentification()->serialize()] = $top_item;
						self::$items[$top_item->getProviderIdentification()->serialize()] = $top_item;
					}
				}
				foreach ($this->providers as $provider) {
					foreach ($provider->getStaticSubItems() as $sub_item) {
						if ($sub_item instanceof hasTitle && $this->information) {
							$sub_item = $this->information->translateItemForUser($sub_item);
						}
						if ($sub_item instanceof isChild && $sub_item->hasParent()) {
							$new_parent_identification = $this->information->getParent($sub_item);
							$parent_item = $this->getSingleItem($new_parent_identification);
							if ($parent_item->getProviderIdentification() instanceof NullIdentification) {
								self::$items[$parent_item->getProviderIdentification()->serialize()] = $parent_item;
								self::$topitems[$parent_item->getProviderIdentification()->serialize()] = $parent_item;
								$sub_item->overrideParent($parent_item->getProviderIdentification());
							} else {
								$sub_item->overrideParent($new_parent_identification);
							}
							if (isset(self::$topitems[$sub_item->getParent()->serialize()]) && self::$topitems[$sub_item->getParent()->serialize()] instanceof isParent) {
								self::$topitems[$sub_item->getParent()->serialize()]->appendChild($sub_item);
							}
						}
						self::$items[$sub_item->getProviderIdentification()->serialize()] = $sub_item; // register them always since they could be lost
					}
				}
			} catch (\Throwable $e) {
				throw $e;
			}
		}

		return $this->loaded;
	}
}
