<?php

namespace ILIAS\FileUpload\Processor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use Psr\Http\Message\StreamInterface;

/**
 * Class WhitelistExtensionPreProcessor
 *
 * PreProcessor which allows only whitelisted file extensions.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0.0
 */
final class WhitelistExtensionPreProcessor implements PreProcessor
{

    /**
     * @var string[]
     */
    private $whitelist;


    /**
     * WhitelistExtensionPreProcessor constructor.
     *
     * Example:
     * ['jpg', 'svg', 'png']
     *
     * Matches:
     * example.jpg
     * example.svg
     * example.png
     *
     * No Match:
     * example.apng
     * example.png.exe
     * ...
     *
     * @param \string[] $whitelist The file extensions which should be whitelisted.
     */
    public function __construct(array $whitelist)
    {
        $this->whitelist = $whitelist;
    }


    /**
     * @inheritDoc
     */
    public function process(FileStream $stream, Metadata $metadata)
    {
        if ($this->isWhitelisted($metadata->getFilename())) {
            return new ProcessingStatus(ProcessingStatus::OK, 'Extension complies with whitelist.');
        }

        return new ProcessingStatus(ProcessingStatus::REJECTED, 'Extension don\'t complies with whitelist.');
    }


    private function isWhitelisted($filename)
    {
        $extensions = explode('.', $filename);
        $extension = null;

        if (count($extensions) === 1) {
            $extension = '';
        } else {
            $extension = end($extensions);
        }

        return in_array(strtolower($extension), $this->whitelist);
    }
}
