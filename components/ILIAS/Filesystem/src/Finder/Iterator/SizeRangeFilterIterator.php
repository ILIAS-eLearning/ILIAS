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

use ILIAS\Data\DataSize;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\DTO\Metadata;
use ILIAS\Filesystem\Finder\Comparator\NumberComparator;

/**
 * @extends \FilterIterator<non-empty-string, Metadata>
 */
class SizeRangeFilterIterator extends \FilterIterator
{
    /** @var list<NumberComparator> */
    private array $comparators;

    /**
     * @param \Iterator<non-empty-string, Metadata> $iterator The Iterator to filter
     */
    public function __construct(
        private readonly Filesystem $filesystem,
        \Iterator $iterator,
        NumberComparator ...$comparators
    ) {
        $this->comparators = $comparators;

        parent::__construct($iterator);
    }

    public function accept(): bool
    {
        /** @var Metadata $metadata */
        $metadata = $this->current();

        if (!$metadata->isFile()) {
            return true;
        }

        if (!$this->filesystem->has($metadata->getPath())) {
            return false;
        }

        $size = $this->filesystem->getSize($metadata->getPath(), DataSize::Byte);
        foreach ($this->comparators as $compare) {
            if (!$compare->test((string) $size->getSize())) {
                return false;
            }
        }

        return true;
    }
}
