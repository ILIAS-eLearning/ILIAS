<?php

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use Psr\Http\Message\StreamInterface;
use ILIAS\FileUpload\Processor\PreProcessor;

/**
 * Class ilVirusScannerPreProcessor
 *
 * PreProcessor which denies all infected files if virusscanner is activated
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @since   5.3
 * @version 1.0.0
 */
final class ilVirusScannerPreProcessor implements PreProcessor
{

    /**
     * @var \ilVirusScanner
     */
    protected $scanner;


    /**
     * ilVirusScannerPreProcessor constructor.
     *
     * @param \ilVirusScanner $scanner
     */
    public function __construct(\ilVirusScanner $scanner)
    {
        $this->scanner = $scanner;
    }


    /**
     * @inheritDoc
     */
    public function process(FileStream $stream, Metadata $metadata) : ProcessingStatus
    {
        // $stream->rewind();
        $uri = $stream->getMetadata()["uri"];
        // chmod($uri, 0755); // we must find a way e.g. ClamAV can read the file
        if ($this->scanner->scanFile($uri) !== "") {
            return new ProcessingStatus(ProcessingStatus::REJECTED, 'Virus detected.');
        }

        return new ProcessingStatus(ProcessingStatus::OK, 'No Virus detected.');
    }
}
