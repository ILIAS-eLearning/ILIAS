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

use ILIAS\Filesystem\Stream\Stream;
use ILIAS\ResourceStorage\Flavour\Definition\PagesToExtract;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class General implements Extractor
{
    public function readImage(\Imagick $img, Stream $stream, PagesToExtract $definition): \Imagick
    {
        $resource = $stream->detach();
        fseek($resource, 0);
        $img->readImageFile($resource);
        return $img;
    }

    public function getResolution(): int
    {
        return 72;
    }

    public function getTargetFormat(): string
    {
        return 'jpg';
    }

    public function getBackground(): \ImagickPixel
    {
        return new \ImagickPixel('white');
    }

    public function getRemoveColor(): ?\ImagickPixel
    {
        return null;
    }

    public function getAlphaChannel(): int
    {
        return \Imagick::ALPHACHANNEL_REMOVE;
    }

}
