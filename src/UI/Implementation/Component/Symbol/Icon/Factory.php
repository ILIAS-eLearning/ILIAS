<?php declare(strict_types=1);

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
 
namespace ILIAS\UI\Implementation\Component\Symbol\Icon;

use ILIAS\UI\Component\Symbol\Icon as I;

class Factory implements I\Factory
{
    /**
     * @inheritdoc
     */
    public function standard(
        string $name,
        string $label,
        string $size = 'small',
        bool $is_disabled = false
    ) : I\Standard {
        return new Standard($name, $label, $size, $is_disabled);
    }

    /**
     * @inheritdoc
     */
    public function custom(
        string $icon_path,
        string $label,
        string $size = 'small',
        bool $is_disabled = false
    ) : I\Custom {
        return new Custom($icon_path, $label, $size, $is_disabled);
    }
}
