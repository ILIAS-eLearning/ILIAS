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

namespace ILIAS\Filesystem\Util\Convert;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\Filesystem\Stream\Streams;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 * @codeCoverageIgnore Nothing interesting to test here
 */
class Converters
{
    private Images $images;
    private LegacyImages $legacy_images;

    public function __construct()
    {
        $this->images = new Images();
        $this->legacy_images = new LegacyImages();
    }

    public function images(): Images
    {
        return $this->images;
    }

    public function legacyImages(): LegacyImages
    {
        return $this->legacy_images;
    }
}
