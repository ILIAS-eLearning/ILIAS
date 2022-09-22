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

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Processor\PreProcessor;
use ILIAS\FileUpload\MimeType;

/**
 * Class ilCountPDFPagesPreProcessors
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilCountPDFPagesPreProcessors implements PreProcessor
{
    public const PAGE_COUNT = 'page_count';
    private ilCountPDFPages $processor;

    public function __construct()
    {
        $this->processor = new ilCountPDFPages();
    }

    public function process(FileStream $stream, Metadata $metadata): \ILIAS\FileUpload\DTO\ProcessingStatus
    {
        if (defined('PATH_TO_GHOSTSCRIPT')
            && PATH_TO_GHOSTSCRIPT !== ""
            && $metadata->getMimeType() == MimeType::APPLICATION__PDF
        ) {
            $path_to_pdf = $stream->getMetadata('uri');
            $metadata->additionalMetaData()->put(
                self::PAGE_COUNT,
                (string) $this->processor->extractAmountOfPagesByPath($path_to_pdf)
            );
        }

        return new ProcessingStatus(
            ProcessingStatus::OK,
            'ilCountPDFPagesPreProcessors'
        );
    }
}
