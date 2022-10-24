<?php

namespace ILIAS\FileUpload\Processor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use Psr\Http\Message\StreamInterface;

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
    private array $whitelist;


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
    public function process(FileStream $stream, Metadata $metadata): ProcessingStatus
    {
        if ($this->isWhitelisted($metadata->getFilename())) {
            return new ProcessingStatus(ProcessingStatus::OK, 'Extension complies with whitelist.');
        }

        return new ProcessingStatus(ProcessingStatus::REJECTED, 'Extension don\'t complies with whitelist.');
    }


    private function isWhitelisted(string $filename): bool
    {
        $extensions = explode('.', $filename);

        $extension = count($extensions) === 1 ? '' : end($extensions);

        return in_array(strtolower($extension), $this->whitelist);
    }
}
