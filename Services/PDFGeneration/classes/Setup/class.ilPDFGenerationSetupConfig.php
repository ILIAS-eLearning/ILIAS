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

use ILIAS\Setup;

/**
 * @deprecated
 */
class ilPDFGenerationSetupConfig implements Setup\Config
{
    protected ?string $path_to_phantom_js;

    public function __construct(?string $path_to_phantom_js)
    {
        $this->path_to_phantom_js = $this->toLinuxConvention($path_to_phantom_js);
    }

    protected function toLinuxConvention(?string $p): ?string
    {
        if (!$p) {
            return null;
        }

        return preg_replace("/\\\\/", "/", $p);
    }

    public function getPathToPhantomJS(): ?string
    {
        return $this->path_to_phantom_js;
    }
}
