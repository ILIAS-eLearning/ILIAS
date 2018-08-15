<?php namespace ILIAS\UX\Collector;

use ILIAS\UX\MainMenu\Slate\Slate;

/**
 * Class Main
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
	 * @return Slate[]
	 */
	public function getStackedSlates(): array {
		return array(); // TODO implement
	}
}
