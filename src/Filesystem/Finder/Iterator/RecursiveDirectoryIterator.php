<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Finder\Iterator;

use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Filesystem;

/**
 * Class RecursiveDirectoryIterator
 * @package ILIAS\Filesystem\Finder\Iterator
 * @author  Michael Jansen <mjansen@databay.de>
 */
class RecursiveDirectoryIterator implements \RecursiveIterator
{
	/** @var Filesystem */
	private $filesystem;

	/** @var string */
	protected $dir;

	/** @var Metadata[] */
	protected $files = [];

	/**
	 * RecursiveDirectoryIterator constructor.
	 * @param Filesystem $filesystem
	 * @param string     $dir
	 */
	public function __construct(Filesystem $filesystem, string $dir)
	{
		$this->filesystem = $filesystem;
		$this->dir = $dir;
	}

	/**
	 * @inheritdoc
	 */
	public function key()
	{
		return key($this->files);
	}

	/**
	 * @inheritdoc
	 */
	public function next()
	{
		next($this->files);
	}

	/**
	 * @inheritdoc
	 * @return Metadata
	 */
	public function current()
	{
		return current($this->files);
	}

	/**
	 * @inheritdoc
	 */
	public function valid()
	{
		return $this->current() instanceof Metadata;
	}

	/**
	 * @inheritdoc
	 */
	public function rewind()
	{
		$contents = $this->filesystem->listContents($this->dir, false);
		$this->files = array_combine(array_map(function (Metadata $metadata) {
			return $metadata->getPath();
		}, $contents), $contents);
	}

	/**
	 * @inheritdoc
	 */
	public function hasChildren()
	{
		return $this->current()->isDir();
	}

	/**
	 * @inheritdoc
	 */
	public function getChildren()
	{
		return new self($this->filesystem, $this->current()->getPath());
	}
}