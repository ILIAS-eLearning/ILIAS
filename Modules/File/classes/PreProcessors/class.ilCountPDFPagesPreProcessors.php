<?php

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Processor\PreProcessor;
use ILIAS\FileUpload\MimeType;

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
 * Class ilCountPDFPagesPreProcessors
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilCountPDFPagesPreProcessors implements PreProcessor
{
    const PAGE_COUNT = 'page_count';


    /**
     * @inheritdoc
     */
    public function process(FileStream $stream, Metadata $metadata): \ILIAS\FileUpload\DTO\ProcessingStatus
    {
        if ($metadata->getMimeType() == MimeType::APPLICATION__PDF
            && PATH_TO_GHOSTSCRIPT != ""
        ) {
            $PATH_TO_PDF = $stream->getMetadata('uri');
            $arg = "-q -dNODISPLAY -c \"($PATH_TO_PDF) (r) file runpdfbegin pdfpagecount = quit\";";
            $return = ilShellUtil::execQuoted(PATH_TO_GHOSTSCRIPT, $arg);

            $metadata->additionalMetaData()->put(self::PAGE_COUNT, (string) $return[0]);
        }

        return new ProcessingStatus(ProcessingStatus::OK, 'ilCountPDFPagesPreProcessors');
    }
}
