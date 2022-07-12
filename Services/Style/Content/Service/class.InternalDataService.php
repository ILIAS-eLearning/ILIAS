<?php declare(strict_types = 1);

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

namespace ILIAS\Style\Content;

use ILIAS\Data\DataSize;

/**
 * Content style data object factory
 * @author Alexander Killing <killing@leifos.de>
 */
class InternalDataService
{
    public function __construct()
    {
    }

    public function characteristic(
        string $type,
        string $characteristic,
        bool $hide,
        array $titles,
        int $style_id = 0,
        int $order_nr = 0,
        bool $outdated = false
    ) : Characteristic {
        $c = new Characteristic(
            $type,
            $characteristic,
            $hide,
            $titles,
            $order_nr,
            $outdated
        );
        if ($style_id > 0) {
            $c = $c->withStyleId($style_id);
        }
        return $c;
    }

    // image
    public function image(
        string $path,
        DataSize $size,
        int $width,
        int $height
    ) : Image {
        return new Image(
            $path,
            $size,
            $width,
            $height
        );
    }
}
