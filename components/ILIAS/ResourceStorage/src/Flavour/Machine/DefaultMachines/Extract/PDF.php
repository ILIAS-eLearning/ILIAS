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

namespace ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines\Extract;

use ILIAS\ResourceStorage\Flavour\Definition\PagesToExtract;
use ILIAS\Filesystem\Stream\Stream;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class PDF extends General implements Extractor
{
    #[\Override]
    public function getResolution(): int
    {
        return 96;
    }

    #[\Override]
    public function readImage(\Imagick $img, Stream $stream, PagesToExtract $definition): \Imagick
    {
        // Using ghostscript to extract pages from PDFs is faster and more reliable. If ghostscript is available, use it.
        if (defined('PATH_TO_GHOSTSCRIPT') && PATH_TO_GHOSTSCRIPT !== "") {
            // extract one single page as image using ghostscript in cli and add it to the imagick object
            $file_path = $stream->getMetadata()['uri'];
            $start_page = 1;

            $cmd = PATH_TO_GHOSTSCRIPT . " -q -dNODISPLAY -dNOSAFER -c \"($file_path) (r) file runpdfbegin pdfpagecount = quit\";";
            $pages_in_file = (int) shell_exec($cmd);

            $end_page = min($pages_in_file, $definition->getMaxPages());
            for ($i = $start_page; $i <= $end_page; $i++) {
                // run ghostscript in cli and return the image to stdout
                $cmd = PATH_TO_GHOSTSCRIPT . " -dNOPAUSE -sDEVICE=png16m -r"
                    . $this->getResolution()
                    . " -dFirstPage=" . $i
                    . " -dLastPage=" . $i .
                    " -sOutputFile=- -q " . escapeshellarg((string) $file_path);
                $pages_in_file = shell_exec($cmd);
                $page = new \Imagick();
                $page->readImageBlob($pages_in_file);
                $img->addImage($page);
            }

            return $img;
        }
        // otherwise we try to extract the pages using the Imagick API
        $resource = $stream->detach();
        fseek($resource, 0);
        $img->readImageFile($resource);
        return $img;
    }

}
