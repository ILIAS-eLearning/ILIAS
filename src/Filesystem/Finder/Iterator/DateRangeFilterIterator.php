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

use FilterIterator;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Finder\Comparator\DateComparator;
use ILIAS\Filesystem\DTO\Metadata;
use InvalidArgumentException;
use Iterator as PhpIterator;

/**
 * Class DateRangeFilterIterator
 * @package ILIAS\Filesystem\Finder\Iterator
 * @author  Michael Jansen <mjansen@databay.de>
 */
class DateRangeFilterIterator extends FilterIterator
{
    /** @var DateComparator[] */
    private array $comparators = [];

    /**
     * @param PhpIterator      $iterator    The Iterator to filter
     * @param DateComparator[] $comparators An array of DateComparator instances
     * @throws InvalidArgumentException
     */
    public function __construct(private Filesystem $filesystem, PhpIterator $iterator, array $comparators)
    {
        array_walk($comparators, static function ($comparator): void {
            if (!($comparator instanceof DateComparator)) {
                throw new InvalidArgumentException(
                    sprintf(
                        'Invalid comparator given: %s',
                        $comparator::class
                    )
                );
            }
        });
        $this->comparators = $comparators;

        parent::__construct($iterator);
    }

    /**
     * @inheritdoc
     */
    public function accept(): bool
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
