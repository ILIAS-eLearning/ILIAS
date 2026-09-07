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

declare(strict_types=1);

namespace ILIAS\VirusScanner\Upload;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Processor\PreProcessor;

/**
 * Scans uploaded files for viruses via a lazily resolved scanner instance.
 * If no scanner is configured the file passes through.
 */
class VirusScannerPreProcessor implements PreProcessor
{
    /**
     * @param \Closure(): ?\ilVirusScanner $scanner_factory
     */
    public function __construct(private readonly \Closure $scanner_factory)
    {
    }

    public function process(FileStream $stream, Metadata $metadata): ProcessingStatus
    {
        $scanner = ($this->scanner_factory)();
        if ($scanner === null) {
            return new ProcessingStatus(ProcessingStatus::OK, 'No virus scanner configured.');
        }
        $uri = $stream->getMetadata()['uri'];
        if ($scanner->scanFile($uri) !== '') {
            return new ProcessingStatus(ProcessingStatus::DENIED, 'Virus detected.');
        }
        return new ProcessingStatus(ProcessingStatus::OK, 'No Virus detected.');
    }
}
