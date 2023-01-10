<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\Filesystem\Finder\Iterator;

use ILIAS\Filesystem\Finder\Comparator\NumberComparator;
use InvalidArgumentException;
use RecursiveIteratorIterator;

/**
 * Class DepthRangeFilterIterator
 * @package ILIAS\Filesystem\Finder\Iterator
 * @author  Michael Jansen <mjansen@databay.de>
 */
class DepthRangeFilterIterator extends \FilterIterator
{
    private int $minDepth = 0;

    /**
     * DepthRangeFilterIterator constructor.
     * @param NumberComparator[] $comparators
     * @throws InvalidArgumentException
     */
    public function __construct(RecursiveIteratorIterator $iterator, array $comparators)
    {
        array_walk($comparators, static function ($comparator): void {
            if (!($comparator instanceof NumberComparator)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid comparator given: %s',
                        $comparator::class
                    )
                );
            }
        });

        $minDepth = 0;
        $maxDepth = PHP_INT_MAX;

        foreach ($comparators as $comparator) {
            switch ($comparator->getOperator()) {
                case '>':
                    $minDepth = (int) $comparator->getTarget() + 1;
                    break;
                case '>=':
                    $minDepth = (int) $comparator->getTarget();
                    break;
                case '<':
                    $maxDepth = (int) $comparator->getTarget() - 1;
                    break;
                case '<=':
                    $maxDepth = (int) $comparator->getTarget();
                    break;
                default:
                    $minDepth = $maxDepth = (int) $comparator->getTarget();
            }
        }

        $this->minDepth = $minDepth;
        $iterator->setMaxDepth(PHP_INT_MAX === $maxDepth ? -1 : $maxDepth);

        parent::__construct($iterator);
    }

    /**
     * @inheritdoc
     */
    public function accept(): bool
    {
        return $this->getInnerIterator()->getDepth() >= $this->minDepth;
    }
}
