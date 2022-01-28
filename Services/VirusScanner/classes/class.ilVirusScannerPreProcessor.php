<?php

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Processor\PreProcessor;

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

    protected \ilVirusScanner $scanner;


    /**
     * ilVirusScannerPreProcessor constructor.
     *
     * @param ilVirusScanner $scanner
     */
    public function __construct(ilVirusScanner $scanner)
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
