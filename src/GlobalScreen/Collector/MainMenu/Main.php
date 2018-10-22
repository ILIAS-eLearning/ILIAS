<?php namespace ILIAS\GlobalScreen\Collector\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\MainMenu\hasTitle;
use ILIAS\GlobalScreen\MainMenu\isChild;
use ILIAS\GlobalScreen\MainMenu\isItem;
use ILIAS\GlobalScreen\MainMenu\isParent;
use ILIAS\GlobalScreen\MainMenu\isTopItem;
use ILIAS\GlobalScreen\Provider\Provider;
use ILIAS\GlobalScreen\Provider\StaticProvider\StaticMainMenuProvider;

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
	 * @var TypeHandler[]
	 */
	private static $typehandlers = [];
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
		foreach (self::$topitems as $top_item) {
			if (!$this->checkAvailability($top_item)) {
				continue;
			}
			if ($top_item instanceof isTopItem && $this->information) {
				if ($top_item instanceof isParent) {
					$children = [];
					/**
					 * @var $top_item  isParent
					 * @var $child     isChild
					 */
					foreach ($top_item->getChildren() as $child) {
						if (!$this->checkAvailability($child)) {
							continue;
						}
						$child = $this->applyTypeHandler($child);
						$position_of_sub_item = $this->information->getPositionOfSubItem($child);
						if (isset($children[$position_of_sub_item])) {
							$position_of_sub_item = max(array_keys($children)) + 1;
						}
						$children[$position_of_sub_item] = $child;
					}
					ksort($children);
					$top_item = $top_item->withChildren($children);
				}
				$top_item = $this->applyTypeHandler($top_item);
				$position_of_top_item = $this->information->getPositionOfTopItem($top_item);
				if (isset($top_items[$position_of_top_item])) {
					$position_of_top_item = max(array_keys($top_items)) + 1;
				}
				$top_items[$position_of_top_item] = $top_item;
			}
		}
		ksort($top_items);

		return $top_items;
	}


	/**
	 * @param isItem $item
	 *
	 * @return bool
	 */

	private function checkAvailability(isItem $item): bool {
		$is_visible = $item->isVisible();
		$is_item_active = $this->information->isItemActive($item);
		$is_always_available = $item->isAlwaysAvailable();

		return !(!$is_visible || !$is_item_active && !$is_always_available);
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
			return $this->getLostItem($identification);
		}
	}


	/**
	 * @param IdentificationInterface $identification
	 *
	 * @return isTopItem
	 */
	private function getLostItem(IdentificationInterface $identification): isTopItem {
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
				$this->loadTopItems();
				$this->loadSubItems();
				foreach ($this->providers as $provider) {
					if ($provider instanceof StaticMainMenuProvider) {
						foreach ($provider->provideTypeHandlers() as $type_handler) {
							if (isset(self::$typehandlers[$type_handler->matchesForType()])) {
								throw new \LogicException("Can't register two Handlers for Type {$type_handler->matchesForType()}");
							}
							self::$typehandlers[$type_handler->matchesForType()] = $type_handler;
						}
					}
				}
			} catch (\Throwable $e) {
				throw $e;
			}
		}

		return $this->loaded;
	}


	/**
	 * @return Provider|mixed
	 */
	private function loadTopItems() {
		foreach ($this->providers as $provider) {
			foreach ($provider->getStaticTopItems() as $top_item) {
				if ($top_item instanceof hasTitle && $this->information) {
					$top_item = $this->information->translateItemForUser($top_item);
				}
				self::$topitems[$top_item->getProviderIdentification()->serialize()] = $top_item;
				self::$items[$top_item->getProviderIdentification()->serialize()] = $top_item;
			}
		}

		return $provider;
	}


	private function loadSubItems() {
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
	}


	/**
	 * @param isItem $item
	 *
	 * @return isItem
	 */
	private function applyTypeHandler(isItem $item): isItem {
		if ($this->hasTypeHandler($item)) {
			$item = $this->getHandlerForItem($item)->enrichItem($item);
		}

		return $item;
	}


	/**
	 * @param isItem $item
	 *
	 * @return bool
	 */
	public function hasTypeHandler(isItem $item): bool {
		$type = get_class($item);

		return isset(self::$typehandlers[$type]);
	}


	/**
	 * @param isItem $item
	 *
	 * @return TypeHandler
	 */
	public function getHandlerForItem(isItem $item): TypeHandler {
		if (!$this->hasTypeHandler($item)) {
			return new BaseTypeHandler();
		}
		/**
		 * @var $handler TypeHandler
		 */
		$type = get_class($item);
		$handler = self::$typehandlers[$type];

		return $handler;
	}
}
