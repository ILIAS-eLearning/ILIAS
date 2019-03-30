<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Finder\Iterator;

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Finder\Comparator\DateComparator;
use ILIAS\Filesystem\DTO\Metadata;

/**
 * Class DateRangeFilterIterator
 * @package ILIAS\Filesystem\Finder\Iterator
 * @author  Michael Jansen <mjansen@databay.de>
 */
class DateRangeFilterIterator extends \FilterIterator
{
	/** @var FileSystem */
	private $filesystem;

	/** @var DateComparator[] */
	private $comparators = [];

	/**
	 * @param Filesystem       $filesystem
	 * @param \Iterator        $iterator    The Iterator to filter
	 * @param DateComparator[] $comparators An array of DateComparator instances
	 */
	public function __construct(FileSystem $filesystem, \Iterator $iterator, array $comparators)
	{
		array_walk($comparators, function ($comparator) {
			if (!($comparator instanceof DateComparator)) {
				if (is_object($comparator)) {
					throw new \InvalidArgumentException(sprintf('Invalid comparator given: %s',
						get_class($comparator)));
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

		$timestamp = $this->filesystem->getTimestamp($metadata->getPath());
		foreach ($this->comparators as $compare) {
			if (!$compare->test($timestamp->format('U'))) {
				return false;
			}
		}

		return true;
	}
}