<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Modal\InterruptiveItem;

use ILIAS\UI\Component\Modal\InterruptiveItem as ItemInterface;
use ILIAS\UI\Component\Image\Image;

/**
 * @author Stefan Wanzenried <sw@studer-raimann.ch>
 */
class Factory implements ItemInterface\Factory
{
    /**
     * @inheritdoc
     */
    public function standard(
        string $id,
        string $title,
        Image $icon = null,
        string $description = ''
    ): ItemInterface\Standard {
        return new Standard($id, $title, $icon, $description);
    }

    public function keyValue(
        string $id,
        string $key,
        string $value
    ): ItemInterface\KeyValue {
        return new KeyValue($id, $key, $value);
    }
}
