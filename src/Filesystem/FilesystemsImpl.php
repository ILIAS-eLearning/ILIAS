<?php

namespace ILIAS\Filesystem;

/**
 * Class FilesystemsImpl
 *
 * The Filesystems implementation holds the configuration for the filesystem service.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 *
 */
class FilesystemsImpl implements Filesystems {

	/**
	 * @var ServiceConfiguration $configuration
	 */
	private $configuration;


	/**
	 * FilesystemsImpl constructor.
	 *
	 * @param ServiceConfiguration $configuration
	 */
	public function __construct(ServiceConfiguration $configuration) {

		if(is_null($configuration))
			throw new \InvalidArgumentException("Configuration must not be null.");

		$this->configuration = $configuration;
	}


	/**
	 * @inheritDoc
	 */
	public function web() {
		return $this->configuration->getWeb();
	}


	/**
	 * @inheritDoc
	 */
	public function storage() {
		return $this->configuration->getStorage();
	}


	/**
	 * @inheritDoc
	 */
	public function temp() {
		return $this->configuration->getTemp();
	}


	/**
	 * @inheritDoc
	 */
	public function customizing() {
		return $this->configuration->getCustomizing();
	}
}