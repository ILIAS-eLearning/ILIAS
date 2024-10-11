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

namespace ILIAS\UI\Implementation\Component\Modal\InterruptiveItem;

use ILIAS\UI\Component\Modal\InterruptiveItem as ItemInterface;
use ILIAS\UI\Component\Image\Image;

class Factory implements ItemInterface\Factory
{
    public function standard(
        string $id,
        string $title,
        Image $icon = null,
        string $description = '',
        string $parameter_name = ItemInterface\InterruptiveItem::DEFAULT_PARAMETER_NAME
    ): ItemInterface\Standard {
        return new Standard($id, $parameter_name, $title, $icon, $description);
    }

    public function keyValue(
        string $id,
        string $key,
        string $value,
        string $parameter_name = ItemInterface\InterruptiveItem::DEFAULT_PARAMETER_NAME
    ): ItemInterface\KeyValue {
        return new KeyValue($id, $parameter_name, $key, $value);
    }
}
