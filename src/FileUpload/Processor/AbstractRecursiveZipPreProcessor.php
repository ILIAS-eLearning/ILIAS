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
 * Class InsecureFilenameSanitizerPreProcessor
 *
 * PreProcessor which checks for file with potentially dangerous names
 *
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class AbstractRecursiveZipPreProcessor implements PreProcessor
{
    use IsMimeTypeOrExtension;
    /**
     * @param string $path to a file
     * @return bool false leads to rejection, true to accept
     */
    abstract protected function checkPath(string $path) : bool;

    abstract protected function getRejectionMessage() : string;

    abstract protected function getOKMessage() : string;

    final public function process(FileStream $stream, Metadata $metadata)
    {
        if ($this->isFileAZip($metadata)) {
            try {
                $zip_file_path = $stream->getMetadata('uri');
                $zip = new \ZipArchive();
                $zip->open($zip_file_path);

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $original_path = $zip->getNameIndex($i);
                    if (!$this->checkPath($original_path)) {
                        return new ProcessingStatus(ProcessingStatus::REJECTED, $this->getRejectionMessage());
                    }
                }
                $zip->close();
            } catch (\Exception $e) {
                return new ProcessingStatus(ProcessingStatus::REJECTED, $e->getMessage());
            }
        }

        if (!$this->checkPath($metadata->getFilename())) {
            return new ProcessingStatus(ProcessingStatus::REJECTED, $this->getRejectionMessage());
        }

        return new ProcessingStatus(ProcessingStatus::OK, $this->getOKMessage());
    }

    private function isFileAZip(Metadata $metadata) : bool
    {
        return $this->isMimeTypeOrExtension(
            $metadata,
            'zip',
            ['application/zip', 'application/x-zip-compressed']
        );
    }
}
