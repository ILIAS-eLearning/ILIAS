<?php

namespace ILIAS\FileUpload\Processor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use Psr\Http\Message\StreamInterface;

/**
 * Class VirusScannerPreProcessor
 *
 * PreProcessor which denies all infected files if virusscanner is activated
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @since   5.3
 * @version 1.0.0
 */
final class VirusScannerPreProcessor implements PreProcessor
{

    /**
     * @var \ilVirusScanner
     */
    protected $scanner;


    /**
     * VirusScannerPreProcessor constructor.
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
    public function process(FileStream $stream, Metadata $metadata)
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
