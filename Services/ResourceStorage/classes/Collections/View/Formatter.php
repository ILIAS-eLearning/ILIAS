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

namespace ILIAS\Services\ResourceStorage\Collections\View;

use ILIAS\Data\DataSize;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait Formatter
{
    protected function formatSize(int $size): string
    {
        $size_factor = 1000;
        $unit = match (true) {
            $size > $size_factor * $size_factor * $size_factor => DataSize::GB,
            $size > $size_factor * $size_factor => DataSize::MB,
            $size > $size_factor => DataSize::KB,
            default => DataSize::Byte,
        };

        $size = (int) (round((float) $size / (float) $unit, 2) * (float) $unit);

        return (string) (new DataSize($size, $unit));
    }
}
