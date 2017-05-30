<?php

namespace ILIAS\Filesystem\DTO;

/**
 * Class Metadata
 *
 * This class holds all default metadata send by the filesystem adapters.
 * Metadata instances are immutable.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 */
final class Metadata {

	/**
	 * @var string $basename
	 */
	private $basename;
	/**
	 * @var string $path
	 */
	private $path;
	/**
	 * @var string $type
	 */
	private $type;


	/**
	 * Metadata constructor.
	 *
	 * @internal
	 *
	 * @param string $basename The basename of the file / directory.
	 * @param string $path     The path to the file / directory.
	 * @param string $type     The file type.
	 *
	 * @throws \InvalidArgumentException Thrown if the type of the given arguments are not correct.
	 */
	public function __construct($basename, $path, $type) {

		if(!is_string($basename))
			throw new \InvalidArgumentException("Basename must be of type string.");

		if(!is_string($path))
			throw new \InvalidArgumentException("Path must be of type string.");

		if(!is_string($type))
			throw new \InvalidArgumentException("Type must be of type string.");

		$this->basename = $basename;
		$this->path = $path;
		$this->type = $type;
	}


	/**
	 * @return string
	 */
	public function getBasename() {
		return $this->basename;
	}


	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}


	/**
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}
}