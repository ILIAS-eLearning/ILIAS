<?php namespace ILIAS\GlobalScreen\Collector\MainMenu;

use ILIAS\GlobalScreen\MainMenu\Slate\Slate;

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
	 * This will return all available slates, stacked based on the configuration
	 * in "Administration" and for the visibility of the currently user.
	 * Additionally this will filter sequent Dividers to avoid double Dividers
	 * in the UI.
	 *
	 * @return Slate[]
	 */
	public function getStackedTopItems(): array {
		return array(); // TODO implementation will be done separately
	}
}
