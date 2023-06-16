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
        $filename = $metadata->getFilename();

        // remove some special characters
        $filename = \ILIAS\Filesystem\Util::sanitizeFileName($filename);

        $metadata->setFilename(Util::normalizeRelativePath($filename));

        return new ProcessingStatus(ProcessingStatus::OK, 'Filename changed');
    }
}
