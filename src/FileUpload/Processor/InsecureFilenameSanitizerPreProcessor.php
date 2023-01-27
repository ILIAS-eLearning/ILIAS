<?php

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

    private $prohibited_names = [
        '...'
    ];

    /**
     * @inheritDoc
     */
    public function process(FileStream $stream, Metadata $metadata)
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
