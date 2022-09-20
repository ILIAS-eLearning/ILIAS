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

namespace ILIAS\UI\Implementation\Component\Symbol\Icon;

use ILIAS\UI\Component as C;

class Custom extends Icon implements C\Symbol\Icon\Custom
{
    private string $icon_path;

    public function __construct(string $icon_path, string $label, string $size, bool $is_disabled)
    {
        $this->checkArgIsElement(
            "size",
            $size,
            self::$possible_sizes,
            implode('/', self::$possible_sizes)
        );
        $this->name = 'custom';
        $this->icon_path = $icon_path;
        $this->label = $label;
        $this->size = $size;
        $this->is_disabled = $is_disabled;
    }

    /**
     * @inheritdoc
     */
    public function getIconPath(): string
    {
        return $this->icon_path;
    }
}
