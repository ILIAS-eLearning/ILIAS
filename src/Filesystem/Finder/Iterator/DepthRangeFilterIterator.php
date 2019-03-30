<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Finder\Iterator;

use ILIAS\Filesystem\Finder\Comparator\NumberComparator;

/**
 * Class DepthRangeFilterIterator
 * @package ILIAS\Filesystem\Finder\Iterator
 * @author  Michael Jansen <mjansen@databay.de>
 */
class DepthRangeFilterIterator extends \FilterIterator
{
	/** @var int */
	private $minDepth = 0;

	/**
	 * DepthRangeFilterIterator constructor.
	 * @param \RecursiveIteratorIterator $iterator
	 * @param NumberComparator[]         $comparators
	 */
	public function __construct(\RecursiveIteratorIterator $iterator, array $comparators)
	{
		array_walk($comparators, function ($comparator) {
			if (!($comparator instanceof NumberComparator)) {
				if (is_object($comparator)) {
					throw new \InvalidArgumentException(sprintf('Invalid comparator given: %s',
						get_class($comparator)));
				}

				throw new \InvalidArgumentException(sprintf('Invalid comparator given: %s', gettype($comparator)));
			}
		});

		$minDepth = 0;
		$maxDepth = PHP_INT_MAX;

		foreach ($comparators as $comparator) {
			switch ($comparator->getOperator()) {
				case '>':
					$minDepth = (int)$comparator->getTarget() + 1;
					break;
				case '>=':
					$minDepth = (int)$comparator->getTarget();
					break;
				case '<':
					$maxDepth = (int)$comparator->getTarget() - 1;
					break;
				case '<=':
					$maxDepth = (int)$comparator->getTarget();
					break;
				default:
					$minDepth = $maxDepth = (int)$comparator->getTarget();
			}
		}

		$this->minDepth = $minDepth;
		$iterator->setMaxDepth(PHP_INT_MAX === $maxDepth ? -1 : $maxDepth);

		parent::__construct($iterator);
	}

	/**
	 * @inheritdoc
	 */
	public function accept()
	{
		return $this->getInnerIterator()->getDepth() >= $this->minDepth;
	}
}