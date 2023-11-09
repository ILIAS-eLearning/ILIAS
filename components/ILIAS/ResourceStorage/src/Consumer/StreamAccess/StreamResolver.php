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

namespace ILIAS\ResourceStorage\Consumer\StreamAccess;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\ResourceStorage\Flavour\Flavour;
use ILIAS\ResourceStorage\Revision\Revision;
use ILIAS\ResourceStorage\StorageHandler\StorageHandlerFactory;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @internal
 */
class StreamResolver
{
    public function __construct(private FileStream $stream)
    {
    }

    public function getStream(): FileStream
    {
        return $this->stream;
    }

    public function isInMemory(): bool
    {
        return $this->stream->getMetadata('uri') === StreamAccess::PHP_MEMORY;
    }
}
