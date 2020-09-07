<?php

namespace ILIAS\FileUpload\Processor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use Psr\Http\Message\StreamInterface;

/**
 * Class BlacklistExtensionPreProcessor
 *
 * PreProcessor which denies all blacklisted file extensions.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 */
final class BlacklistExtensionPreProcessor implements PreProcessor
{

    /**
     * @var string[]
     */
    private $blacklist;

    /**
     * BlacklistExtensionPreProcessor constructor.
     *
     * Example:
     * ['jpg', 'svg', 'png', '']
     *
     * Matches:
     * example.jpg
     * example.svg
     * example.png
     * example
     *
     * No Match:
     * example.apng
     * example.png.exe
     * ...
     *
     * @param \string[] $blacklist The file extensions which should be blacklisted.
     */
    public function __construct(array $blacklist)
    {
        $this->blacklist = $blacklist;
    }


    /**
     * @inheritDoc
     */
    public function process(FileStream $stream, Metadata $metadata)
    {
        if ($this->isBlacklisted($metadata->getFilename())) {
            return new ProcessingStatus(ProcessingStatus::REJECTED, 'Extension is blacklisted.');
        }

        return new ProcessingStatus(ProcessingStatus::OK, 'Extension is not blacklisted.');
    }


    /**
     * Checks if the current filename has a listed extension. (*.png, *.mp4 etc ...)
     *
     * @param string $filename The filename which should be checked.
     *
     * @return bool True if the extension is listed, otherwise false.
     */
    private function isBlacklisted($filename)
    {
        $extensions = explode('.', $filename);
        $extension = null;

        if (count($extensions) <= 1) {
            $extension = '';
        } else {
            $extension = end($extensions);
        }

        return in_array($extension, $this->blacklist);
    }
}
