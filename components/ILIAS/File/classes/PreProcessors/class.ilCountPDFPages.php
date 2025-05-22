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

use ILIAS\ResourceStorage\Services;
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
    /**
     * @readonly
     */
    private Services $irss;
    private bool $postscript_available = false;
    private bool $imagick_available = false;

    public function __construct()
    {
        global $DIC;
        $this->irss = $DIC->resourceStorage();
        $this->postscript_available = (defined('PATH_TO_GHOSTSCRIPT') && PATH_TO_GHOSTSCRIPT !== "");
        $this->imagick_available = class_exists('Imagick');
    }

    public function isAvailable(): bool
    {
        return $this->postscript_available || $this->imagick_available;
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
        if (!$this->postscript_available && !$this->imagick_available) {
            return null;
        }

        // first we try using Imagick
        if ($this->imagick_available) {
            $pages = null;
            try {
                $imagick = new Imagick();
                $imagick->pingImage($path_to_pdf);

                return $imagick->getNumberImages();
            } catch (Throwable) {
                // Imagick is not available or another error occured
            }
        }

        if ($this->postscript_available) {
            $arg = "-q -dNODISPLAY -dNOSAFER -c \"($path_to_pdf) (r) file runpdfbegin pdfpagecount = quit\";";
            $return = ilShellUtil::execQuoted(PATH_TO_GHOSTSCRIPT, $arg);
            if (!isset($return[0]) || ($pages = (int) $return[0]) === 0) {
                return null;
            }

            return $pages;
        }

        return null;
    }
}
