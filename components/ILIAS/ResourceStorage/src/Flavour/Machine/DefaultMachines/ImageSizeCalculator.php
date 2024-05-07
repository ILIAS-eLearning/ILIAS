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

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
trait ImageSizeCalculator
{
    private function calculateWidthHeight(float $original_width, float $original_height, int $max_size): array
    {
        if ($original_width === $original_height) {
            return [$max_size, $max_size];
        }

        if ($original_width > $original_height) {
            $columns = $max_size;
            $rows = (float) ($max_size * $original_height / $original_width);
            return [$columns, $rows];
        }

        $columns = (float) ($max_size * $original_width / $original_height);
        $rows = $max_size;
        return [$columns, $rows];
    }

    private function calculateWidthHeightFromImage(\Imagick $original, int $max_size): array
    {
        return $this->calculateWidthHeight(
            $original->getImageWidth(),
            $original->getImageHeight(),
            $max_size
        );
    }
}
