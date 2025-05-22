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

use ILIAS\Setup;
use ILIAS\Data\Password;

class ilStyleSetupConfig implements Setup\Config
{
    /**
     * @var bool
     */
    protected $manage_system_styles;

    /**
     * @var string|null
     */
    protected $path_to_scss;

    public function __construct(
        bool $manage_system_styles,
        ?string $path_to_scss
    ) {
        $this->manage_system_styles = $manage_system_styles;
        $this->path_to_scss = $this->toLinuxConvention($path_to_scss);
    }

    protected function toLinuxConvention(?string $p): ?string
    {
        if (!$p) {
            return null;
        }
        return preg_replace("/\\\\/", "/", $p);
    }

    public function getManageSystemStyles(): bool
    {
        return $this->manage_system_styles;
    }

    public function getPathToScss(): ?string
    {
        return $this->path_to_scss;
    }
}
