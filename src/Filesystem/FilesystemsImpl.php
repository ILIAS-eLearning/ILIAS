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
	 * @var Filesystem $storage
	 */
	private $storage;
	/**
	 * @var Filesystem $storage
	 */
	private $web;
	/**
	 * @var Filesystem $storage
	 */
	private $temp;
	/**
	 * @var Filesystem $storage
	 */
	private $customizing;


	/**
	 * FilesystemsImpl constructor.
	 *
	 * @param Filesystem $storage
	 * @param Filesystem $web
	 * @param Filesystem $temp
	 * @param Filesystem $customizing
	 */
	public function __construct(Filesystem $storage, Filesystem $web, Filesystem $temp, Filesystem $customizing) {
		$this->storage = $storage;
		$this->web = $web;
		$this->temp = $temp;
		$this->customizing = $customizing;
	}


	/**
	 * @inheritDoc
	 */
	public function web() {
		return $this->web;
	}


	/**
	 * @inheritDoc
	 */
	public function storage() {
		return $this->storage;
	}


	/**
	 * @inheritDoc
	 */
	public function temp() {
		return $this->temp;
	}


	/**
	 * @inheritDoc
	 */
	public function customizing() {
		return $this->customizing;
	}
}