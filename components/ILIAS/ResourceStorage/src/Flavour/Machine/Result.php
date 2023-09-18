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

namespace ILIAS\ResourceStorage\Flavour\Machine;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Flavour\Definition\FlavourDefinition;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Result
{
    protected FlavourDefinition $definition;
    protected FileStream $stream;
    protected int $index = 0;
    protected bool $storeable = true;

    public function __construct(
        FlavourDefinition $definition,
        FileStream $stream,
        int $index = 0,
        bool $storeable = true
    ) {
        $this->definition = $definition;
        $this->stream = $stream;
        $this->index = $index;
        $this->storeable = $storeable;
    }

    public function getIndex(): int
    {
        return $this->index;
    }


    public function getDefinition(): FlavourDefinition
    {
        return $this->definition;
    }

    public function getStream(): FileStream
    {
        return $this->stream;
    }

    public function isStoreable(): bool
    {
        return $this->storeable;
    }
}
