<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Finder\Iterator;

use ILIAS\Data\DataSize;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Finder\Comparator\NumberComparator;

/**
 * Class SizeRangeFilterIterator
 * @package ILIAS\Filesystem\Finder\Iterator
 * @author Michael Jansen <mjansen@databay.de>
 */
class SizeRangeFilterIterator extends \FilterIterator
{
	/** @var FileSystem */
	private $filesystem;

	/** @var NumberComparator[] */
	private $comparators = [];

	/**
	 * @param Filesystem $filesystem
	 * @param \Iterator $iterator The Iterator to filter
	 * @param NumberComparator[] $comparators An array of NumberComparator instances
	 */
	public function __construct(FileSystem $filesystem, \Iterator $iterator, array $comparators)
	{
		array_walk($comparators, function($comparator) {
			if (!($comparator instanceof NumberComparator)) {
				if (is_object($comparator)) {
					throw new \InvalidArgumentException(sprintf('Invalid comparator given: %s', get_class($comparator)));
				}

				throw new \InvalidArgumentException(sprintf('Invalid comparator given: %s', gettype($comparator)));
			}
		});

		$this->filesystem = $filesystem;
		$this->comparators = $comparators;

		parent::__construct($iterator);
	}

	/**
	 * @inheritdoc
	 */
	public function accept()
	{
		/** @var Metadata $metadata */
		$metadata = $this->current();
		if (!$this->filesystem->has($metadata->getPath())) {
			return false;
		}

		$size = $this->filesystem->getSize($metadata->getPath(), DataSize::Byte);
		foreach ($this->comparators as $compare) {
			if (!$compare->test((string)$size->getSize())) {
				return false;
			}
		}

		return true;
	}
}