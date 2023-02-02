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
use ILIAS\ResourceStorage\Identification\ResourceIdentification;

/**
 * Class ilCountPDFPages
 *
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal This class is not part of the public ILIAS API and may change at any time.
 */
class ilCountPDFPages
{
    private \ILIAS\ResourceStorage\Services $irss;
    private bool $postscript_available = false;

    public function __construct()
    {
        global $DIC;
        $this->irss = $DIC->resourceStorage();
        $this->postscript_available = (defined('PATH_TO_GHOSTSCRIPT') && PATH_TO_GHOSTSCRIPT !== "");
    }

    public function isAvailable(): bool
    {
        return $this->postscript_available;
    }

    public function extractAmountOfPagesByRID(ResourceIdentification $rid): ?int
    {
        if (!$this->postscript_available) {
            return null;
        }
        $revision = $this->irss->manage()->getCurrentRevision($rid);
        $info = $revision->getInformation();
        if ($info->getMimeType() !== MimeType::APPLICATION__PDF) {
            return null;
        }
        $consumer = $this->irss->consume()->stream($rid);
        $path_to_pdf = $consumer->getStream()->getMetadata('uri');
        return $this->extractAmountOfPagesByPath($path_to_pdf);
    }

    public function extractAmountOfPagesByPath(string $path_to_pdf): ?int
    {
        if (!$this->postscript_available) {
            return null;
        }
        $arg = "-q -dNODISPLAY -dNOSAFER -c \"($path_to_pdf) (r) file runpdfbegin pdfpagecount = quit\";";
        $return = ilShellUtil::execQuoted(PATH_TO_GHOSTSCRIPT, $arg);

        return (int) $return[0] ?? null;
    }
}
