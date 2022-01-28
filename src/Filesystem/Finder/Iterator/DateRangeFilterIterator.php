<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Finder\Iterator;

use FilterIterator;
use ILIAS\Filesystem\Filesystem;
use ILIAS\Filesystem\Finder\Comparator\DateComparator;
use ILIAS\Filesystem\DTO\Metadata;
use InvalidArgumentException;
use Iterator as PhpIterator;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Class DateRangeFilterIterator
 * @package ILIAS\Filesystem\Finder\Iterator
 * @author  Michael Jansen <mjansen@databay.de>
 */
class DateRangeFilterIterator extends FilterIterator
{
    private FileSystem $filesystem;
    /** @var DateComparator[] */
    private array $comparators = [];

    /**
     * @param Filesystem $filesystem
     * @param PhpIterator $iterator The Iterator to filter
     * @param DateComparator[] $comparators An array of DateComparator instances
     * @throws InvalidArgumentException
     */
    public function __construct(Filesystem $filesystem, PhpIterator $iterator, array $comparators)
    {
        array_walk($comparators, static function ($comparator) : void {
            if (!($comparator instanceof DateComparator)) {
                if (is_object($comparator)) {
                    throw new InvalidArgumentException(sprintf(
                        'Invalid comparator given: %s',
                        get_class($comparator)
                    ));
                }

                throw new InvalidArgumentException(sprintf('Invalid comparator given: %s', gettype($comparator)));
            }
        });

        $this->filesystem = $filesystem;
        $this->comparators = $comparators;

        parent::__construct($iterator);
    }

    /**
     * @inheritdoc
     */
    public function accept() : bool
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
