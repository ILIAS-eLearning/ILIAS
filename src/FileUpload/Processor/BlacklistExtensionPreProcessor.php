<?php

namespace ILIAS\FileUpload\Processor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Class BlacklistExtensionPreProcessor
 * PreProcessor which denies all blacklisted file extensions.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0.0
 */
class BlacklistExtensionPreProcessor implements PreProcessor
{
    private string $reason;
    /**
     * @var string[]
     */
    private array $blacklist;
    
    /**
     * BlacklistExtensionPreProcessor constructor.
     * Example:
     * ['jpg', 'svg', 'png', '']
     * Matches:
     * example.jpg
     * example.svg
     * example.png
     * example
     * No Match:
     * example.apng
     * example.png.exe
     * ...
     *
     * @param \string[] $blacklist The file extensions which should be blacklisted.
     */
    public function __construct(array $blacklist, string $reason = 'Extension is blacklisted.')
    {
        $this->blacklist = $blacklist;
        $this->reason = $reason;
    }
    
    /**
     * @inheritDoc
     */
    public function process(FileStream $stream, Metadata $metadata) : ProcessingStatus
    {
        if ($this->isBlacklisted($metadata, $stream)) {
            return new ProcessingStatus(ProcessingStatus::REJECTED, $this->reason);
        }
        
        return new ProcessingStatus(ProcessingStatus::OK, 'Extension is not blacklisted.');
    }
    
    /**
     * Checks if the current filename has a listed extension. (*.png, *.mp4 etc ...)
     *
     * @return bool True if the extension is listed, otherwise false.
     */
    private function isBlacklisted(Metadata $metadata, FileStream $stream) : bool
    {
        $filename = $metadata->getFilename();
        $extension = $this->getExtensionForFilename($filename);
        
        if (strtolower($extension) === 'zip') {
            $zip_file_path = $stream->getMetadata('uri');
            $zip = new \ZipArchive();
            $zip->open($zip_file_path);
            
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $original_path = $zip->getNameIndex($i);
                $extension_sub_file = $this->getExtensionForFilename($original_path);
                if ($extension_sub_file === '') {
                    continue;
                }
                if (in_array($extension_sub_file, $this->blacklist, true)) {
                    $zip->close();
                    $this->reason = $this->reason .= " ($original_path in $filename)";
                    
                    return true;
                }
            }
            $zip->close();
        }
        
        $in_array = in_array($extension, $this->blacklist, true);
        if (!$in_array) {
            $this->reason = $this->reason .= " ($filename)";
        }
        return $in_array;
    }
    
    private function getExtensionForFilename(string $filename) : string
    {
        $extensions = explode('.', $filename);
        
        return count($extensions) <= 1 ? '' : strtolower(end($extensions));
    }
}
