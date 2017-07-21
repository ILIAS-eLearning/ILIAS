<?php

namespace ILIAS\Filesystem\Provider;

use ILIAS\Filesystem\Provider\Configuration\LocalConfig;
use ILIAS\Filesystem\Provider\FlySystem\FlySystemFilesystemFactory;

/**
 * Class DelegatingFilesystemFactory
 *
 * The delegating filesystem factory delegates the instance creation to the
 * factory of the concrete implementation.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 */
class DelegatingFilesystemFactory implements FilesystemFactory {

	private $implementation;


	/**
	 * DelegatingFilesystemFactory constructor.
	 */
	public function __construct() {

		/*
		 * ---------- ABSTRACTION SWITCH -------------
		 * Change the factory to switch to another filesystem abstraction!
		 * current: FlySystem from the php league
		 * -------------------------------------------
		 */
		$this->implementation = new FlySystemFilesystemFactory();
	}


	/**
	 * @inheritDoc
	 */
	public function getLocal(LocalConfig $config) {
		return $this->implementation->getLocal($config);
	}
}