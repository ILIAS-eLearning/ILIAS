<?php namespace ILIAS\GlobalScreen\Collector\MainMenu;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\MainMenu\isChild;
use ILIAS\GlobalScreen\MainMenu\isItem;
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
	 * @var array|Provider[]
	 */
	protected $providers;


	/**
	 * Main constructor.
	 *
	 * @param array $providers
	 */
	public function __construct(array $providers) {
		if (self::$constructed === true) {
			throw new \LogicException("only one Instance of Main Collector is possible");
		}
		$this->providers = $providers;
		self::$constructed = true;
	}


	/**
	 * This will return all available topitems, stacked based on the configuration
	 * in "Administration" and for the visibility of the currently user.
	 * Additionally this will filter sequent Separators to avoid double Separators
	 * in the UI.
	 *
	 * @param bool $with_invisible
	 *
	 * @return isTopItem[]
	 */
	public function getStackedTopItems(bool $with_invisible = false): array {
		$this->load();
		$top_items = [];
		foreach (self::$items as $item) {
			if ($item instanceof isTopItem) {
				$id = $item->getProviderIdentification()->serialize();
				$top_items[$id] = $item;
			}
		}

		foreach (self::$items as $item) {
			if (!$item->isVisible()) {
				continue;
			}
			if ($item instanceof isChild && $item->hasParent()) {
				$parent_id = $item->getParent()->serialize();
				$top_items[$parent_id]->appendChild($item);
			}
		}

		return $top_items;
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
						self::$items[$sub_item->getProviderIdentification()->serialize()] = $sub_item;
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
