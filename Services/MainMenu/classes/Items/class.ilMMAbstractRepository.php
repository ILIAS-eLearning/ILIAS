<?php

use ILIAS\GlobalScreen\Collector\StorageFacade;

/**
 * Class ilMMAbstractRepository
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class ilMMAbstractRepository {

	/**
	 * @var StorageFacade
	 */
	protected $storage;


	/**
	 * ilMMAbstractRepository constructor.
	 *
	 * @param StorageFacade $storage
	 */
	public function __construct(StorageFacade $storage) {
		$this->storage = $storage;
	}
}
