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

namespace ILIAS\ResourceStorage\Flavour\Machine\DefaultMachines;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @noinspection AutoloadingIssuesInspection
 */
trait GdImageToStreamTrait
{
    /**
     * Currently this is the only way to make a FileStream from a GD image resource.
     * As soon as this is possible diretly, we can just switch the implementation here.
     */
    protected function to(\GdImage $image, int $quality = null): FileStream
    {
        ob_start();
        imagejpeg($image, null, $quality ?? 75);
        $stringdata = ob_get_contents();
        imagedestroy($image);
        ob_end_clean();

        return Streams::ofString($stringdata);
    }

    protected function from(FileStream $stream): ?\GdImage
    {
        if ($stream->getSize() > ini_get('memory_limit')) {
            // return null; // we could stop here if the memorsy-limit is reached, but we must convert things like 1000M to bytes then
        }

        try {
            return imagecreatefromstring((string)$stream);
        } catch (\Throwable $t) {
            return null;
        }
    }
}
