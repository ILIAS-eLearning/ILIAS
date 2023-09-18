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

    /**
     * @param PhpIterator $iterator The Iterator to filter
     * @param int         $mode     The mode (self::ALL or self::ONLY_FILES or self::ONLY_DIRECTORIES)
     */
    public function __construct(PhpIterator $iterator, private int $mode)
    {
        parent::__construct($iterator);
    }

    /**
     * @inheritdoc
     */
    public function accept(): bool
    {
        /** @var Metadata $metadata */
        $metadata = $this->current();
        if (self::ONLY_DIRECTORIES === (self::ONLY_DIRECTORIES&$this->mode) && $metadata->isFile()) {
            return false;
        }
        if (self::ONLY_FILES !== (self::ONLY_FILES&$this->mode)) {
            return true;
        }
        if (!$metadata->isDir()) {
            return true;
        }
        return false;
    }
}
