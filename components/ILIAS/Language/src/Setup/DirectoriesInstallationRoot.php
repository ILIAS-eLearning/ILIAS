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

namespace ILIAS\Language\Setup;

use ILIAS\Environment\Configuration\Installation\Directories;

/**
 * Reads the root from the Directories of the installation on every call, never at construction.
 */
final readonly class DirectoriesInstallationRoot implements InstallationRoot
{
    public function __construct(
        private Directories $directories
    ) {
    }

    public function getPath(): string
    {
        return $this->directories->getRoot();
    }
}
