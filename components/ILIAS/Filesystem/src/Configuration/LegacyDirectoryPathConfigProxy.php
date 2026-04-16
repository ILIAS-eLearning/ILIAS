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

namespace ILIAS\Filesystem\Configuration;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 * @internal Delegated to global Constants
 */
class LegacyDirectoryPathConfigProxy implements DirectoryPathConfig
{
    public function getWebDirectoryPath(): string
    {
        return ILIAS_ABSOLUTE_PATH . '/public/' . ILIAS_WEB_DIR . '/' . CLIENT_ID;
    }

    public function getStorageDirectoryPath(): string
    {
        return ILIAS_DATA_DIR . '/' . CLIENT_ID;
    }

    public function getCustomizingDirectoyPath(): string
    {
        return ILIAS_ABSOLUTE_PATH . '/public/' . 'Customizing';
    }

    public function getNodeModulesDirectoryPath(): string
    {
        return ILIAS_ABSOLUTE_PATH . '/' . 'node_modules';
    }

    public function getLibsDirectoryPath(): string
    {
        return ILIAS_ABSOLUTE_PATH . '/' . 'vendor';
    }

    public function getTempDirectoryPath(): string
    {
        return ILIAS_DATA_DIR . '/' . CLIENT_ID . '/temp';
    }

}
