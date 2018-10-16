<?php

use ILIAS\GlobalScreen\Collector\StorageFacade;

/**
 * Class ilMMAbstractRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilMMAbstractRepository {

	/**
	 * @var ilDBInterface
	 */
	protected $db;
	/**
	 * @var ilGlobalCache
	 */
	protected $cache;


	/**
	 * ilMMAbstractRepository constructor.
	 *
	 * @param StorageFacade $storage
	 */
	public function __construct(StorageFacade $storage) {
		$this->db = $storage->db();
		$this->cache = $storage->cache();
	}
}
