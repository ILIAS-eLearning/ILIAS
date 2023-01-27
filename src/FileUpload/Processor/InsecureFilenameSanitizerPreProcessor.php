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
 * Class InsecureFilenameSanitizerPreProcessor
 *
 * PreProcessor which chechs for file with potentially dangerous names
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
final class InsecureFilenameSanitizerPreProcessor implements PreProcessor
{
    private array $prohibited_names = [
        '...'
    ];

    /**
     * @inheritDoc
     */
    public function process(FileStream $stream, Metadata $metadata): ProcessingStatus
    {
        if ($this->containsInsecureFileNames($metadata, $stream)) {
            return new ProcessingStatus(ProcessingStatus::REJECTED, 'A Security Issue has been detected, File-upload aborted...');
        }

        return new ProcessingStatus(ProcessingStatus::OK, 'Extension is not blacklisted.');
    }

    private function containsInsecureFileNames(Metadata $metadata, FileStream $stream): bool
    {
        $filename = $metadata->getFilename();

        if (strpos($filename, 'zip') !== false) {
            $zip_file_path = $stream->getMetadata('uri');
            $zip = new \ZipArchive();
            $zip->open($zip_file_path);

            for ($i = 0; $i < $zip->numFiles; $i++) {
                $original_path = $zip->getNameIndex($i);
                if ($this->doesPathContainInsecureName($original_path)) {
                    return true;
                }
            }
            $zip->close();
        }

        return $this->doesPathContainInsecureName($filename);
    }

    private function doesPathContainInsecureName(string $path): bool
    {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('/\/+/', '/', $path);
        $path = trim($path, '/');
        $parts = explode('/', $path);
        foreach ($parts as $part) {
            if (in_array($part, $this->prohibited_names)) {
                return true;
            }
        }
        return false;
    }
}
