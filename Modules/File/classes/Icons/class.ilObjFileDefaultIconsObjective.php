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

namespace ILIAS\File\Icon;

use ILIAS\Setup\Environment;
use ilResourceStorageMigrationHelper;
use ilDBInterface;

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
class ilObjFileDefaultIconsObjective implements \ILIAS\Setup\Objective
{
    private const PATH_DEFAULT_ICON_DIR = __DIR__ . "/../../../../templates/default/images/default_file_icons/";

    /**
     * @inheritDoc
     */
    public function getHash(): string
    {
        return hash(
            "sha256",
            get_class($this)
        );
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return "Creation of the default icons for file objects.";
    }

    /**
     * @inheritDoc
     */
    public function isNotable(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getPreconditions(Environment $environment): array
    {
        return ilResourceStorageMigrationHelper::getPreconditions();
    }

    /**
     * @inheritDoc
     */
    public function achieve(Environment $environment): Environment
    {
        $helper = new ilResourceStorageMigrationHelper(
            new ilObjFileIconStakeholder(),
            $environment
        );
        /**
         * @var $db ilDBInterface
         */
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);

        $scan_result = scandir(self::PATH_DEFAULT_ICON_DIR);
        $default_icon_filenames = preg_grep("/^icon_file_/", $scan_result);
        foreach ($default_icon_filenames as $default_icon_filename) {
            $icon_file_prefix = "icon_file_";
            $icon_file_suffix = ".svg";
            $suffix = str_replace($icon_file_prefix, "", $default_icon_filename);
            $suffix = str_replace($icon_file_suffix, "", $suffix);

            //check if there is an existing db entry for a default icon with this suffix
            $query = "SELECT * FROM " . IconDatabaseRepository::SUFFIX_TABLE_NAME . " AS s"
                . " INNER JOIN " . IconDatabaseRepository::ICON_TABLE_NAME . " AS i"
                . " ON s." . IconDatabaseRepository::ICON_RESOURCE_IDENTIFICATION . " = i." . IconDatabaseRepository::ICON_RESOURCE_IDENTIFICATION
                . " WHERE s." . IconDatabaseRepository::SUFFIX . " = %s AND i." . IconDatabaseRepository::IS_DEFAULT_ICON . " = %s";
            $statement = $db->queryF(
                $query,
                ['text', 'integer'],
                [$suffix, 1]
            );
            $num_matches = $db->numRows($statement);

            //skip copying the file to the resource storage and creating the new db entries if this default icon has been added already
            if ($num_matches > 0) {
                continue;
            }

            // copy icon to a temp dir before using the moveToStorage function to prevent losing the original file
            $path_temp_dir = \ilFileUtils::ilTempnam();
            $temp_dir_created = mkdir($path_temp_dir);
            if (!$temp_dir_created) {
                continue;
            }
            $path_default_file_icon = self::PATH_DEFAULT_ICON_DIR . $default_icon_filename;
            $path_temp_default_file_icon = $path_temp_dir . "/" . $default_icon_filename;
            $copied_to_temp = copy($path_default_file_icon, $path_temp_default_file_icon);
            if (!$copied_to_temp) {
                continue;
            }
            //move copy of default icon in temp dir to resource storage
            $resource_identification = $helper->movePathToStorage($path_temp_default_file_icon, 6);
            $rid = $resource_identification->serialize();
            //create icon & icon suffix db entry for newly added default icon
            $db->insert(
                IconDatabaseRepository::SUFFIX_TABLE_NAME,
                [
                    IconDatabaseRepository::ICON_RESOURCE_IDENTIFICATION => ['text', $rid],
                    IconDatabaseRepository::SUFFIX => ['text', $suffix],
                ]
            );
            $db->insert(
                IconDatabaseRepository::ICON_TABLE_NAME,
                [
                    IconDatabaseRepository::ICON_RESOURCE_IDENTIFICATION => ['text', $rid],
                    IconDatabaseRepository::ICON_ACTIVE => ['integer', true],
                    IconDatabaseRepository::IS_DEFAULT_ICON => ['integer', true]
                ]
            );
        }

        return $environment;
    }

    /**
     * @inheritDoc
     */
    public function isApplicable(Environment $environment): bool
    {
        /**
         * @var $db ilDBInterface
         */
        $db = $environment->getResource(Environment::RESOURCE_DATABASE);
        if ($db->tableExists(IconDatabaseRepository::ICON_TABLE_NAME)) {
            $scan_result = scandir(self::PATH_DEFAULT_ICON_DIR);
            $num_default_icons_in_dir = is_countable(preg_grep("/^icon_file_/", $scan_result)) ? count(
                preg_grep("/^icon_file_/", $scan_result)
            ) : 0;

            $query = "SELECT * FROM " . IconDatabaseRepository::ICON_TABLE_NAME . " WHERE " . IconDatabaseRepository::IS_DEFAULT_ICON . " = 1";
            $statement = $db->query($query);
            $num_default_icons_in_db = $db->numRows($statement);

            if ($num_default_icons_in_db < $num_default_icons_in_dir) {
                return true;
            }
        }
        return false;
    }
}
