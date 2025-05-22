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

namespace ILIAS\UI\Implementation\Component\Image;

use ILIAS\UI\Component\Image as I;

class Factory implements I\Factory
{
    public function standard(string $src, string $alt): Image
    {
        return new Image(I\Image::STANDARD, $src, $alt);
    }

    public function responsive(string $src, string $alt): Image
    {
        return new Image(I\Image::RESPONSIVE, $src, $alt);
    }
}
