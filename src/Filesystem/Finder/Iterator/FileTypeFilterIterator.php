<?php
declare(strict_types=1);

namespace ILIAS\Filesystem\Finder\Iterator;

use ILIAS\Filesystem\DTO\Metadata;
use Iterator as PhpIterator;

/**
 * Class FileTypeFilterIterator
 * @package ILIAS\Filesystem\Finder\Iterator
 * @author  Michael Jansen <mjansen@databay.de>
 */
class FileTypeFilterIterator extends \FilterIterator
{
    public const ALL = 0;
    public const ONLY_FILES = 1;
    public const ONLY_DIRECTORIES = 2;

    /** @var int */
    private $mode = self::ALL;

    /**
     * @param PhpIterator $iterator The Iterator to filter
     * @param int $mode The mode (self::ALL or self::ONLY_FILES or self::ONLY_DIRECTORIES)
     */
    public function __construct(PhpIterator $iterator, int $mode)
    {
        $this->mode = $mode;
        parent::__construct($iterator);
    }

    /**
     * @inheritdoc
     */
    public function accept()
    {
        /** @var Metadata $metadata */
        $metadata = $this->current();

        if (self::ONLY_DIRECTORIES === (self::ONLY_DIRECTORIES & $this->mode) && $metadata->isFile()) {
            return false;
        } elseif (self::ONLY_FILES === (self::ONLY_FILES & $this->mode) && $metadata->isDir()) {
            return false;
        }

        return true;
    }
}
