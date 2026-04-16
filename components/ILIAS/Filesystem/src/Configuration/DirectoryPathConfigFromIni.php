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

use ILIAS\Environment\Configuration\Instance\IliasIni;
use ILIAS\Environment\Configuration\Instance\ClientIni;
use ILIAS\Environment\Configuration\Instance\ClientIdProvider;

/**
 * @author   Fabian Schmid <fabian@sr.solutions>
 */
class DirectoryPathConfigFromIni implements DirectoryPathConfig
{
    private string $absolute_path;
    private string $web_dir;
    private string $data_dir;
    private string $client_id;

    public function __construct(
        IliasIni $ilias_ini,
        ClientIni $client_ini,
        ClientIdProvider $client_id_provider,
    ) {
        $this->absolute_path = $ilias_ini->getAbsolutePath();
        $this->web_dir = str_replace('public/', '', $ilias_ini->getClientsPath());
        $this->data_dir = $ilias_ini->getDataDirectory();
        $this->client_id = $client_id_provider->getClientId()->toString();
    }

    public function getWebDirectoryPath(): string
    {
        return $this->absolute_path . '/public/' . $this->web_dir . '/' . $this->client_id;
    }

    public function getStorageDirectoryPath(): string
    {
        return $this->data_dir . '/' . $this->client_id;
    }

    public function getCustomizingDirectoyPath(): string
    {
        return $this->absolute_path . '/public/Customizing';
    }

    public function getNodeModulesDirectoryPath(): string
    {
        return $this->absolute_path . '/node_modules';
    }

    public function getLibsDirectoryPath(): string
    {
        return $this->absolute_path . '/vendor';
    }

    public function getTempDirectoryPath(): string
    {
        return $this->data_dir . '/' . $this->client_id . '/temp';
    }
}
