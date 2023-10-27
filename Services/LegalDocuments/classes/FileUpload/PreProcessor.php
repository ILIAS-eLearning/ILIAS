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

namespace ILIAS\LegalDocuments\FileUpload;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Processor\PreProcessor as PreProcessorInterface;
use Closure;

class PreProcessor implements PreProcessorInterface
{
    /**
     * @param Closure(string): void $fill
     */
    public function __construct(private readonly Closure $fill)
    {
    }

    public function process(FileStream $stream, Metadata $metadata): ProcessingStatus
    {
        ($this->fill)($stream->getContents());

        return new ProcessingStatus(ProcessingStatus::OK, 'idontcare');
    }
}
