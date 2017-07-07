<?php

namespace ILIAS\Filesystem;

/**
 * Class ServiceConfiguration
 *
 * This class holds the filesystems for the filesystem service.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 */
class ServiceConfiguration {

	/**
	 * @var Filesystem $web
	 */
	private $web;
	/**
	 * @var Filesystem $storage
	 */
	private $storage;
	/**
	 * @var Filesystem $temp
	 */
	private $temp;
	/**
	 * @var Filesystem $customizing
	 */
	private $customizing;


	/**
	 * @param Filesystem $web
	 *
	 * @return ServiceConfiguration
	 */
	public function setWeb($web) {
		$this->validateFilesystem($web);

		$this->web = $web;

		return $this;
	}


	/**
	 * @param Filesystem $storage
	 *
	 * @return ServiceConfiguration
	 */
	public function setStorage($storage) {
		$this->validateFilesystem($storage);

		$this->storage = $storage;

		return $this;
	}


	/**
	 * @param Filesystem $temp
	 *
	 * @return ServiceConfiguration
	 */
	public function setTemp($temp) {
		$this->validateFilesystem($temp);

		$this->temp = $temp;

		return $this;
	}


	/**
	 * @param Filesystem $customizing
	 *
	 * @return ServiceConfiguration
	 */
	public function setCustomizing($customizing) {
		$this->validateFilesystem($customizing);

		$this->customizing = $customizing;

		return $this;
	}


	/**
	 * Validates the given filesystem an raise an exception if the filesystem is not valid.
	 *
	 * @param Filesystem $filesystem    The filesystem which should be validated.
	 * @throws \InvalidArgumentException        Thrown if the given filesystem is not valid.
	 */
	private function validateFilesystem($filesystem) {
		if($filesystem instanceof Filesystem)
			throw new \InvalidArgumentException("The given filesystem ist not valid.");
	}


	/**
	 * @return Filesystem
	 */
	public function getWeb() {
		return $this->web;
	}


	/**
	 * @return Filesystem
	 */
	public function getStorage() {
		return $this->storage;
	}


	/**
	 * @return Filesystem
	 */
	public function getTemp() {
		return $this->temp;
	}


	/**
	 * @return Filesystem
	 */
	public function getCustomizing() {
		return $this->customizing;
	}
}