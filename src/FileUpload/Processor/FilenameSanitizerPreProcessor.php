<?php

namespace ILIAS\FileUpload\Processor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use League\Flysystem\Util;

/**
 * Class FilenameSanitizerPreProcessor
 *
 * PreProcessor which overrides the filename with a given one
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @since   5.3
 * @version 1.0.0
 */
final class FilenameSanitizerPreProcessor implements PreProcessor
{

    /**
     * @inheritDoc
     */
    public function process(FileStream $stream, Metadata $metadata)
    {
        $metadata->setFilename(Util::normalizeRelativePath($metadata->getFilename()));

        return new ProcessingStatus(ProcessingStatus::OK, 'Filename changed');
    }
}
