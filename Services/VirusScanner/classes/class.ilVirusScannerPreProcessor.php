<?php declare(strict_types=1);

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

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Processor\PreProcessor;

final class ilVirusScannerPreProcessor implements PreProcessor
{
    protected \ilVirusScanner $scanner;

    public function __construct(ilVirusScanner $scanner)
    {
        $this->scanner = $scanner;
    }

    public function process(FileStream $stream, Metadata $metadata) : ProcessingStatus
    {
        $uri = $stream->getMetadata()["uri"];
        // chmod($uri, 0755); // we must find a way e.g. ClamAV can read the file
        if ($this->scanner->scanFile($uri) !== "") {
            return new ProcessingStatus(ProcessingStatus::REJECTED, 'Virus detected.');
        }

        return new ProcessingStatus(ProcessingStatus::OK, 'No Virus detected.');
    }
}
