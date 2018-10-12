<?php namespace ILIAS\GlobalScreen\Collector\MainMenu;

use ILIAS\GlobalScreen\MainMenu\isTopItem;
use ILIAS\GlobalScreen\MainMenu\Slate\Slate;
use ILIAS\GlobalScreen\MainMenu\TopItem\TopParentItem;

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
	 * @var \ilDBInterface
	 */
	protected $db;
	/**
	 * @var \ilGlobalCacheService
	 */
	protected $cache;


	/**
	 * Main constructor.
	 *
	 * @param \ilDBInterface        $db
	 * @param \ilGlobalCacheService $cache
	 */
	public function __construct(\ilDBInterface $db, \ilGlobalCacheService $cache) {
		$this->db = $db;
		$this->cache = $cache;
	}


	/**
	 * This will return all available topitems, stacked based on the configuration
	 * in "Administration" and for the visibility of the currently user.
	 * Additionally this will filter sequent Separators to avoid double Separators
	 * in the UI.
	 *
	 * @return isTopItem[]
	 */
	public function getStackedTopItems(): array {
		return array(); // TODO implementation will be done separately
	}
}
