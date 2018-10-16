<?php namespace ILIAS\GlobalScreen\Collector\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\MainMenu\hasTitle;
use ILIAS\GlobalScreen\MainMenu\isChild;
use ILIAS\GlobalScreen\MainMenu\isItem;
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
	 * @var ItemSorting
	 */
	private $sorting;
	/**
	 * @var ItemTranslation
	 */
	private $translation;
	/**
	 * @var array|Provider[]
	 */
	protected $providers;


	/**
	 * Main constructor.
	 *
	 * @param array                $providers
	 * @param ItemSorting|null     $sorting
	 * @param ItemTranslation|null $translation
	 */
	public function __construct(array $providers, ItemSorting $sorting = null, ItemTranslation $translation = null) {
		if (self::$constructed === true) {
			throw new \LogicException("only one Instance of Main Collector is possible");
		}
		$this->sorting = $sorting;
		$this->translation = $translation;
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
		$this->load();
		$top_items = [];
		foreach (self::$items as $item) {
			if ($item instanceof hasTitle && $this->translation) {
				$item = $this->translation->translateItemForUser($item);
			}
			if ($item instanceof isTopItem && $this->sorting) {
				$top_items[$this->sorting->getPositionOfTopItem($item)] = $item;
			}
		}
		ksort($top_items);

		return $top_items;
	}


	public function getSubItems(): array {
		$this->load();
		$sub_items = [];
		foreach (self::$items as $item) {
			if ($item instanceof hasTitle && $this->translation) {
				$item = $this->translation->translateItemForUser($item);
			}
			if ($item instanceof isChild && $this->sorting) {
				$sub_items[$this->sorting->getPositionOfSubItem($item)] = $item;
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
