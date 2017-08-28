<?php

use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Processor\PreProcessor;

/**
 * Class ilCountPDFPagesPreProcessors
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilCountPDFPagesPreProcessors implements PreProcessor {

	/**
	 * @inheritdoc
	 */
	public function process(\ILIAS\Filesystem\Stream\FileStream $stream, \ILIAS\FileUpload\DTO\Metadata $metadata) {
		if ($metadata->getMimeType() == ilMimeTypeUtil::APPLICATION__PDF && PATH_TO_GHOSTSCRIPT != "") {
			// gs -q -dNODISPLAY -c "($PATH_TO_PDF) (r) file runpdfbegin pdfpagecount = quit";

			$PATH_TO_PDF = ilUtil::escapeShellArg($stream->getMetadata('uri'));

			$arg = "-q -dNODISPLAY -c \"($PATH_TO_PDF) (r) file runpdfbegin pdfpagecount = quit\";";
			$return = ilUtil::execQuoted(PATH_TO_GHOSTSCRIPT, $arg);
		}

		return new ProcessingStatus(ProcessingStatus::OK, 'ilCountPDFPagesPreProcessors');
	}
}
