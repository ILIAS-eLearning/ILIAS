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

/**
 * @template-covariant TKey
 * @template-covariant TValue
 * @extends \FilterIterator<TKey, TValue>
 */
class DepthRangeFilterIterator extends \FilterIterator
{
    private int $minDepth;

    /**
     * @param \RecursiveIteratorIterator<\RecursiveIterator<TKey, TValue>> $iterator The iterator to filter
     */
    public function __construct(\RecursiveIteratorIterator $iterator, NumberComparator ...$comparators)
    {
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

    public function accept(): bool
    {
        return $this->getInnerIterator()->getDepth() >= $this->minDepth;
    }
}
