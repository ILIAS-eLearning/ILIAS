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
	 * @var ItemInformation|null
	 */
	private $information;
	/**
	 * @var array|Provider[]
	 */
	protected $providers;


	/**
	 * Main constructor.
	 *
	 * @param array                $providers
	 * @param ItemInformation|null $information
	 */
	public function __construct(array $providers, ItemInformation $information = null) {
		if (self::$constructed === true) {
			throw new \LogicException("only one Instance of Main Collector is possible");
		}
		$this->information = $information;
		$this->providers = $providers;
		self::$constructed = true;
	}


	/**
	 * This will return all available topitems, stacked based on the configuration
	 * in "Administration" and for the visibility of the currently user.
	 * Additionally this will filter sequent Separators to avoid double Separators
	 * in the UI.
	 *
	 * @return isTopItem[]
	 */
	public function getStackedTopItemsForPresentation(): array {
		return $this->getStackedTopItems();
	}


	/**
	 * @return isTopItem[]
	 */
	private function getStackedTopItems(): array {
		$this->load();
		$top_items = [];
		foreach (self::$items as $item) {
			if ((!$item->isVisible() || !$this->information->isItemActive($item) && !$item->isAlwaysAvailable())) {
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
						if ((!$child->isVisible() || !$this->information->isItemActive($child) && !$child->isAlwaysAvailable())) {
							continue;
						}
						$children[$this->information->getPositionOfSubItem($child)] = $child;
					}
					ksort($children);
					$item = $item->withChildren($children);
				}
				$top_items[$this->information->getPositionOfTopItem($item)] = $item;
			}
		}
		ksort($top_items);

		return $top_items;
	}


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
	 */
	public function getSingleItem(IdentificationInterface $identification): isItem {
		$this->load();

		return self::$items[$identification->serialize()];
	}


	/**
	 * @return bool
	 */
	private function load(): bool {
		$loaded = false;
		if ($loaded === false || $loaded === null) {
			/**
			 * @var $provider            \ILIAS\GlobalScreen\Provider\StaticProvider\StaticMainMenuProvider
			 * @var $top_item            \ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem
			 * @var $sub_item            \ILIAS\GlobalScreen\MainMenu\isChild
			 */
			try {
				foreach ($this->providers as $provider) {
					foreach ($provider->getStaticTopItems() as $top_item) {
						self::$items[$top_item->getProviderIdentification()->serialize()] = $top_item;
					}
				}
				foreach ($this->providers as $provider) {
					foreach ($provider->getStaticSubItems() as $sub_item) {
						if ($sub_item instanceof isChild && $sub_item->hasParent()) {
							$sub_item->overrideParent($this->information->getParent($sub_item));
							self::$items[$sub_item->getProviderIdentification()->serialize()] = $sub_item;
							self::$items[$sub_item->getProviderIdentification()->serialize()] = $sub_item;
							self::$items[$sub_item->getParent()->serialize()]->appendChild($sub_item);
						}
					}
				}
				$loaded = true;
			} catch (\Throwable $e) {
				throw new \LogicException($e->getMessage());
			}
		}

		return $loaded;
	}
}
