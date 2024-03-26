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

    public function __construct(
        private bool $reset_default = false,
        private bool $reset_all = false
    ) {
    }

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

            $path_default_file_icon = self::PATH_DEFAULT_ICON_DIR . $default_icon_filename;

            //move copy of default icon in temp dir to resource storage
            $resource_identification = $helper->movePathToStorage(
                $path_default_file_icon,
                6,
                null,
                null,
                true
            );

            if ($resource_identification === null) {
                continue;
            }
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

        // this removes all icons from the database and therefore forces a re-creation of the default icons
        if ($this->reset_all) {
            if ($db->tableExists(IconDatabaseRepository::ICON_TABLE_NAME)) {
                $db->manipulate("DELETE FROM " . IconDatabaseRepository::ICON_TABLE_NAME);
            }
            if ($db->tableExists(IconDatabaseRepository::SUFFIX_TABLE_NAME)) {
                $db->manipulate("DELETE FROM " . IconDatabaseRepository::SUFFIX_TABLE_NAME);
            }
            return true;
        }

        // this removes all default icons from the database and therefore forces a re-creation of the default icons but keeps custom icons
        if ($this->reset_default && $db->tableExists(IconDatabaseRepository::ICON_TABLE_NAME)) {
            // we can proceed if icon tables are not available
            if (
                !$db->tableExists(IconDatabaseRepository::SUFFIX_TABLE_NAME)
                && !$db->tableExists(IconDatabaseRepository::ICON_TABLE_NAME)
            ) {
                return true;
            }

            // selects the rids of all default icons
            $query = "SELECT rid FROM " . IconDatabaseRepository::ICON_TABLE_NAME . " WHERE " . IconDatabaseRepository::IS_DEFAULT_ICON . " = 1";
            $statement = $db->query($query);
            $rids = [];
            while ($row = $db->fetchAssoc($statement)) {
                $rids[] = $row[IconDatabaseRepository::ICON_RESOURCE_IDENTIFICATION];
            }
            // delete all default icons
            $db->manipulate(
                "DELETE FROM " . IconDatabaseRepository::ICON_TABLE_NAME . " WHERE " . IconDatabaseRepository::IS_DEFAULT_ICON . " = 1"
            );

            // now we deactivate all custom icons which override the default icons. therefore we must search for the suffixes related to the default icons.
            // read all suffixes of the default icons
            $query = "SELECT " . IconDatabaseRepository::SUFFIX . " FROM " . IconDatabaseRepository::SUFFIX_TABLE_NAME . " WHERE " . $db->in(
                IconDatabaseRepository::ICON_RESOURCE_IDENTIFICATION,
                $rids,
                false,
                'text'
            );
            $statement = $db->query($query);
            $suffixes = [];
            while ($row = $db->fetchAssoc($statement)) {
                $suffixes[] = $row[IconDatabaseRepository::SUFFIX];
            }
            // remove all entries of the default icons
            $db->manipulate(
                "DELETE FROM " . IconDatabaseRepository::SUFFIX_TABLE_NAME . " WHERE " . $db->in(
                    IconDatabaseRepository::ICON_RESOURCE_IDENTIFICATION,
                    $rids,
                    false,
                    'text'
                )
            );

            $rids = [];
            if (!empty($suffixes)) {
                // collect rids of custom icons which override the default icons
                $query = "SELECT " . IconDatabaseRepository::ICON_RESOURCE_IDENTIFICATION . " FROM " . IconDatabaseRepository::SUFFIX_TABLE_NAME . " WHERE " . $db->in(
                    IconDatabaseRepository::SUFFIX,
                    $suffixes,
                    false,
                    'text'
                );
                $statement = $db->query($query);

                while ($row = $db->fetchAssoc($statement)) {
                    $rids[] = $row[IconDatabaseRepository::ICON_RESOURCE_IDENTIFICATION];
                }
            }

            // deactivate all custom icons which override the default icons
            if (!empty($rids)) {
                $db->manipulate(
                    "UPDATE " . IconDatabaseRepository::ICON_TABLE_NAME . " SET " . IconDatabaseRepository::ICON_ACTIVE . " = 0 WHERE " . $db->in(
                        IconDatabaseRepository::ICON_RESOURCE_IDENTIFICATION,
                        $rids,
                        false,
                        'text'
                    )
                );
            }

            // clean up orphaned suffixes
            $db->manipulate(
                "DELETE su FROM " . IconDatabaseRepository::SUFFIX_TABLE_NAME . " su LEFT JOIN " . IconDatabaseRepository::ICON_TABLE_NAME
                . " i ON su." . IconDatabaseRepository::ICON_RESOURCE_IDENTIFICATION . " = i." . IconDatabaseRepository::ICON_RESOURCE_IDENTIFICATION
                . " WHERE i." . IconDatabaseRepository::ICON_RESOURCE_IDENTIFICATION . " IS NULL"
            );

            return true;
        }

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
