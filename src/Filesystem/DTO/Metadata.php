<?php
declare(strict_types=1);

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
	 * @param string $basename  The basename of the file / directory.
	 * @param string $path      The path to the file / directory.
	 * @param string $type      The file type.
	 */
	public function __construct(string $basename, string $path, string $type) {
		$this->basename = $basename;
		$this->path = $path;
		$this->type = $type;
	}


	/**
	 * @return string
	 */
	public function getBasename(): string {
		return $this->basename;
	}


	/**
	 * @return string
	 */
	public function getPath(): string {
		return $this->path;
	}


	/**
	 * @return string
	 */
	public function getType(): string {
		return $this->type;
	}
}