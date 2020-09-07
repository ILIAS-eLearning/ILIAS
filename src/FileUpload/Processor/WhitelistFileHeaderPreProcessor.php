<?php

namespace ILIAS\FileUpload\Processor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\ScalarTypeCheckAware;
use Psr\Http\Message\StreamInterface;

/**
 * Class WhitelistFileHeaderPreProcessor
 *
 * The whitelist file header pre processor rejects all files which do not start with the specified file start.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 */
final class WhitelistFileHeaderPreProcessor implements PreProcessor
{
    use ScalarTypeCheckAware;

    /**
     * @var string $fileHeader
     */
    private $fileHeader;
    /**
     * @var int $fileHeaderLength
     */
    private $fileHeaderLength;


    /**
     * WhitelistFileHeaderPreProcessor constructor.
     *
     * @param string $fileHeader
     */
    public function __construct($fileHeader)
    {
        $this->stringTypeCheck($fileHeader, 'fileHeader');

        $this->fileHeaderLength = strlen($fileHeader);
        $this->fileHeader = $fileHeader;
    }


    /**
     * @inheritDoc
     */
    public function process(FileStream $stream, Metadata $metadata)
    {
        $header = $stream->read($this->fileHeaderLength);
        if (strcmp($this->fileHeader, $header) === 0) {
            return new ProcessingStatus(ProcessingStatus::OK, 'File header complies with whitelist.');
        }

        return new ProcessingStatus(ProcessingStatus::REJECTED, 'File header don\'t complies with whitelist.');
    }
}
