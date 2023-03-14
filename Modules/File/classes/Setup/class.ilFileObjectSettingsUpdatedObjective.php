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

use ILIAS\Refinery;
use ILIAS\Setup;
use ILIAS\Setup\Environment;
use ILIAS\Setup\Objective;

class ilFileObjectSettingsUpdatedObjective implements Setup\Objective
{
    public function getHash(): string
    {
        return hash(
            "sha256",
            get_class($this)
        );
    }

    public function getLabel(): string
    {
        return "File Object Settings have been updated.";
    }

    public function isNotable(): bool
    {
        return false;
    }

    public function getPreconditions(Environment $environment): array
    {
        return [
            new ilDatabaseInitializedObjective(),
            new ilIniFilesLoadedObjective()
        ];
    }

    public function achieve(Environment $environment): Environment
    {
        /**
         * @var $db         ilDBInterface
         * @var $client_ini ilIniFile
         */
        $db = $environment->getResource(Setup\Environment::RESOURCE_DATABASE);
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

        $download_with_uploaded_filename = $client_ini->readVariable(
            'file_access',
            'download_with_uploaded_filename'
        ) === '1';
        $db->manipulateF(
            "INSERT IGNORE settings (module, keyword, value) VALUES (%s, %s, %s)",
            ['text', 'text', 'text'],
            ['file_access', 'download_with_uploaded_filename', $download_with_uploaded_filename ? '1' : '0']
        );

        $use_ascii_characters_only = $client_ini->readVariable(
            'file_access',
            'disable_ascii'
        ) !== '1';

        $db->manipulateF(
            "INSERT IGNORE settings (module, keyword, value) VALUES (%s, %s, %s)",
            ['text', 'text', 'text'],
            ['file_access', 'download_ascii_filename', $use_ascii_characters_only ? '1' : '0']
        );

        $db->manipulate(
            "UPDATE settings SET module = 'file_access' WHERE keyword = 'bgtask_download'"
        );

        $client_ini->removeGroup('file_access');
        $client_ini->write();

        return $environment;
    }

    public function isApplicable(Environment $environment): bool
    {
        /**
         * @var $client_ini ilIniFile
         */
        $client_ini = $environment->getResource(Setup\Environment::RESOURCE_CLIENT_INI);

        return $client_ini->groupExists("file_access");
    }
}
