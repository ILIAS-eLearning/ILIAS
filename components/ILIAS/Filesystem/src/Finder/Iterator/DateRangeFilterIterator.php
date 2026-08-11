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

use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Finder\Comparator\DateComparator;
use ILIAS\Filesystem\DTO\Metadata;

/**
 * @extends \FilterIterator<non-empty-string, Metadata>
 */
class DateRangeFilterIterator extends \FilterIterator
{
    /** @var list<DateComparator> */
    private array $comparators;

    /**
     * @param \Iterator<non-empty-string, Metadata> $iterator The Iterator to filter
     */
    public function __construct(
        private readonly Filesystem $filesystem,
        \Iterator $iterator,
        DateComparator ...$comparators
    ) {
        $this->comparators = $comparators;

        parent::__construct($iterator);
    }

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
