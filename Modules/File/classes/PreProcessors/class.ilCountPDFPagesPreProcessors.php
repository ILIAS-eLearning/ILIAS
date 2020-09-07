<?php

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Processor\PreProcessor;

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
    public function process(FileStream $stream, Metadata $metadata)
    {
        if ($metadata->getMimeType() == ilMimeTypeUtil::APPLICATION__PDF
            && PATH_TO_GHOSTSCRIPT != ""
        ) {
            $PATH_TO_PDF = $stream->getMetadata('uri');
            $arg = "-q -dNODISPLAY -c \"($PATH_TO_PDF) (r) file runpdfbegin pdfpagecount = quit\";";
            $return = ilUtil::execQuoted(PATH_TO_GHOSTSCRIPT, $arg);

            $metadata->additionalMetaData()->put(self::PAGE_COUNT, (string) $return[0]);
        }

        return new ProcessingStatus(ProcessingStatus::OK, 'ilCountPDFPagesPreProcessors');
    }
}
