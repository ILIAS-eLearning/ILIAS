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
    public function process(FileStream $stream, Metadata $metadata): ProcessingStatus
    {
        $metadata->setFilename($this->normalizeRelativePath($metadata->getFilename()));

        return new ProcessingStatus(ProcessingStatus::OK, 'Filename changed');
    }

    private function normalizeRelativePath(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        $path =  preg_replace('#\p{C}+#u', '', $path);
        $parts = [];

        foreach (explode('/', $path) as $part) {
            switch ($part) {
                case '':
                case '.':
                    break;

                case '..':
                    if (empty($parts)) {
                        throw new \LogicException(
                            'Path is outside of the defined root, path: [' . $path . ']'
                        );
                    }
                    array_pop($parts);
                    break;

                default:
                    $parts[] = $part;
                    break;
            }
        }

        return implode('/', $parts);
    }
}
