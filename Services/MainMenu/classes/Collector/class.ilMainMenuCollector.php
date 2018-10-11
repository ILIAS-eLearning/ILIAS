<?php

use ilGlobalCache;
use ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem;

/**
 * Class ilMainMenuCollector
 *
 * This Collector will collect and then provide all available slates from the
 * providers in the whole system, stack them and enrich them will their content
 * based on the configuration in "Administration".
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMainMenuCollector {

	/**
	 * @var \ilDBInterface
	 */
	protected $db;
	/**
	 * @var ilGlobalCache
	 */
	protected $cache;


	/**
	 * ilMainMenuCollector constructor.
	 *
	 * @param ilDBInterface  $db
	 * @param \ilGlobalCache $cache
	 */
	public function __construct(\ilDBInterface $db, ilGlobalCache $cache) {
		$this->db = $db;
		$this->cache = $cache;
	}


	/**
	 * This will return all available slates, stacked based on the configuration
	 * in "Administration" and for the visibility of the currently user.
	 * Additionally this will filter sequent Dividers to avoid double Dividers
	 * in the UI.
	 *
	 * @return TopParentItem[]
	 */
	public function getStackedSlates(): array {
		/**
		 * @var $provider_storage ilGSProviderStorage
		 * @var $provider         \ILIAS\GlobalScreen\Provider\StaticProvider\StaticMainMenuProvider
		 * @var $slate            \ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem
		 * @var $entry            \ILIAS\GlobalScreen\MainMenu\isChild
		 */
		$slates = [];
		foreach (ilGSProviderStorage::get() as $provider_storage) {
			$provider = $provider_storage->getInstance();
			foreach ($provider->getStaticSlates() as $slate) {
				$id = $slate->getProviderIdentification()->serialize();
				$slates[$id] = $slate;
			}

			foreach ($provider->getStaticEntries() as $entry) {
				if (!$entry->isVisible()) {
					continue;
				}
				if ($entry->hasParent()) {
					$parent_id = $entry->getParent()->serialize();
					$slates[$parent_id]->appendChild($entry);
				}
			}
		}

		return $slates;
	}
}
