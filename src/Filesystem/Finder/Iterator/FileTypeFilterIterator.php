<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Finder\Iterator;

use ILIAS\Filesystem\DTO\Metadata;

/**
 * Class FileTypeFilterIterator
 * @package ILIAS\Filesystem\Finder\Iterator
 * @author  Michael Jansen <mjansen@databay.de>
 */
class FileTypeFilterIterator extends \FilterIterator
{
	const ALL = 0;
	const ONLY_FILES = 1;
	const ONLY_DIRECTORIES = 2;

	/** @var int */
	private $mode = self::ALL;

	/**
	 * @param \Iterator $iterator The Iterator to filter
	 * @param int       $mode     The mode (self::ALL or self::ONLY_FILES or self::ONLY_DIRECTORIES)
	 */
	public function __construct(\Iterator $iterator, int $mode)
	{
		$this->mode = $mode;
		parent::__construct($iterator);
	}

	/**
	 * @inheritdoc
	 */
	public function accept()
	{
		/** @var Metadata $metadata */
		$metadata = $this->current();

		if (self::ONLY_DIRECTORIES === (self::ONLY_DIRECTORIES & $this->mode) && $metadata->isFile()) {
			return false;
		} elseif (self::ONLY_FILES === (self::ONLY_FILES & $this->mode) && $metadata->isDir()) {
			return false;
		}

		return true;
	}
}