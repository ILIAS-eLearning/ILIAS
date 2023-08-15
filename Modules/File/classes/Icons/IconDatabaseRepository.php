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

use ILIAS\ResourceStorage\Services;
use ilUtil;

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
class IconDatabaseRepository extends IconAbstractRepository
{
    public const ICON_TABLE_NAME = 'il_file_icon';
    public const ICON_RESOURCE_IDENTIFICATION = 'rid';
    public const ICON_ACTIVE = 'active';
    public const IS_DEFAULT_ICON = 'is_default_icon';
    public const SUFFIX_TABLE_NAME = 'il_file_icon_suffixes';
    public const SUFFIX = 'suffix';
    public const SUFFIXES = 'suffixes';

    private \ilDBInterface $db;
    private Services $irss;

    public function __construct()
    {
        global $DIC;
        parent::__construct();
        $this->db = $DIC->database();
        $this->irss = $DIC->resourceStorage();
    }

    public function createIcon(string $a_rid, bool $a_active, bool $a_is_default_icon, array $a_suffixes): Icon
    {
        $icon = new CustomIcon($a_rid, $a_active, $a_is_default_icon, $a_suffixes);
        foreach ($icon->getSuffixes() as $suffix) {
            $this->db->insert(
                self::SUFFIX_TABLE_NAME,
                [
                    self::ICON_RESOURCE_IDENTIFICATION => ['text', $icon->getRid()],
                    self::SUFFIX => ['text', $suffix],
                ]
            );
        }
        $this->db->insert(
            self::ICON_TABLE_NAME,
            [
                self::ICON_RESOURCE_IDENTIFICATION => ['text', $icon->getRid()],
                self::ICON_ACTIVE => ['integer', $icon->isActive()],
                self::IS_DEFAULT_ICON => ['integer', $icon->isDefaultIcon()]
            ]
        );
        return $icon;
    }

    public function getIconsForFilter(array $filter): array
    {
        $icons = [];

        $query = "SELECT i." . self::ICON_RESOURCE_IDENTIFICATION
            . ", i." . self::ICON_ACTIVE
            . ", i." . self::IS_DEFAULT_ICON
            . ", GROUP_CONCAT(s." . self::SUFFIX . ") AS " . self::SUFFIXES
            . " FROM " . self::ICON_TABLE_NAME . " AS i"
            . " JOIN " . self::SUFFIX_TABLE_NAME . " AS s"
            . " ON " . "i." . self::ICON_RESOURCE_IDENTIFICATION . " = " . "s." . self::ICON_RESOURCE_IDENTIFICATION;

        if ($filter !== []) {
            $query .= " WHERE true ";
            if (($filter['active'] ?? null) !== null && $filter['active'] !== '') {
                $query .= " AND i.active = " . $this->db->quote($filter['active'], 'integer');
            }

            if (($filter['suffixes'] ?? null) !== null && $filter['suffixes'] !== '') {
                $query .= " AND s.suffix LIKE " . $this->db->quote('%' . $filter['suffixes'] . '%', 'text');
            }

            if (($filter['is_default_icon'] ?? null) !== null && $filter['is_default_icon'] !== '') {
                $query .= " AND i.is_default_icon = " . $this->db->quote($filter['is_default_icon'], 'integer');
            }
        }

        $query .= " GROUP BY i." . self::ICON_RESOURCE_IDENTIFICATION;

        $result = $this->db->query($query);

        while ($data = $this->db->fetchAssoc($result)) {
            $icon = new CustomIcon(
                $rid = $data[self::ICON_RESOURCE_IDENTIFICATION],
                (bool) $data[self::ICON_ACTIVE],
                (bool) $data[self::IS_DEFAULT_ICON],
                $this->turnSuffixesStringIntoArray($data[self::SUFFIXES])
            );
            $icons[$rid] = $icon;
        }

        return $icons;
    }

    /**
     * @return array<int|string, \ILIAS\File\Icon\CustomIcon>
     */
    public function getIcons(): array
    {
        $icons = [];

        $query = "SELECT i." . self::ICON_RESOURCE_IDENTIFICATION
            . ", i." . self::ICON_ACTIVE
            . ", i." . self::IS_DEFAULT_ICON
            . ", GROUP_CONCAT(s." . self::SUFFIX . ") AS " . self::SUFFIXES
            . " FROM " . self::ICON_TABLE_NAME . " AS i"
            . " INNER JOIN " . self::SUFFIX_TABLE_NAME . " AS s"
            . " ON " . "i." . self::ICON_RESOURCE_IDENTIFICATION . " = " . "s." . self::ICON_RESOURCE_IDENTIFICATION
            . " GROUP BY i." . self::ICON_RESOURCE_IDENTIFICATION;

        $result = $this->db->query($query);

        while ($data = $this->db->fetchAssoc($result)) {
            $icon = new CustomIcon(
                $rid = $data[self::ICON_RESOURCE_IDENTIFICATION],
                (bool) $data[self::ICON_ACTIVE],
                (bool) $data[self::IS_DEFAULT_ICON],
                $this->turnSuffixesStringIntoArray($data[self::SUFFIXES])
            );
            $icons[$rid] = $icon;
        }

        return $icons;
    }

    public function getIconByRid(string $a_rid): Icon
    {
        $icon = new NullIcon();

        $query = "SELECT i." . self::ICON_RESOURCE_IDENTIFICATION
            . ", i." . self::ICON_ACTIVE
            . ", i." . self::IS_DEFAULT_ICON
            . ", GROUP_CONCAT(s." . self::SUFFIX . ") AS " . self::SUFFIXES
            . " FROM " . self::ICON_TABLE_NAME . " AS i"
            . " INNER JOIN " . self::SUFFIX_TABLE_NAME . " AS s"
            . " ON " . "i." . self::ICON_RESOURCE_IDENTIFICATION . " = " . "s." . self::ICON_RESOURCE_IDENTIFICATION
            . " WHERE i." . self::ICON_RESOURCE_IDENTIFICATION . " = %s"
            . " GROUP BY i." . self::ICON_RESOURCE_IDENTIFICATION;

        $result = $this->db->queryF(
            $query,
            ["text"],
            [$a_rid]
        );

        while ($data = $this->db->fetchAssoc($result)) {
            $icon = new CustomIcon(
                $rid = $data[self::ICON_RESOURCE_IDENTIFICATION],
                (bool) $data[self::ICON_ACTIVE],
                (bool) $data[self::IS_DEFAULT_ICON],
                $this->turnSuffixesStringIntoArray($data[self::SUFFIXES])
            );
        }

        return $icon;
    }

    public function getActiveIconForSuffix(string $a_suffix): Icon
    {
        $rid = null;
        $icon = new NullIcon();

        // Determine the icon's rid first and then determine the icon by its rid.
        // This is done because a query like the one in getIconByRid with a where-clause
        // for the suffix would not return all suffixes of the matching icon.
        $query = "SELECT s." . self::ICON_RESOURCE_IDENTIFICATION . " FROM " . self::SUFFIX_TABLE_NAME . " AS s"
            . " INNER JOIN " . self::ICON_TABLE_NAME . " AS i"
            . " ON s." . self::ICON_RESOURCE_IDENTIFICATION . " = i." . self::ICON_RESOURCE_IDENTIFICATION
            . " WHERE s." . self::SUFFIX . " = %s AND i." . self::ICON_ACTIVE . " = %s";
        $result = $this->db->queryF(
            $query,
            ["text", "integer"],
            [$a_suffix, 1]
        );
        while ($data = $this->db->fetchAssoc($result)) {
            $rid = $data[self::ICON_RESOURCE_IDENTIFICATION];
        }

        if ($rid !== null) {
            return $this->getIconByRid($rid);
        }

        return $icon;
    }

    public function getIconFilePathBySuffix(string $suffix): string
    {
        if ($suffix !== "") {
            $icon = self::getActiveIconForSuffix($suffix);
            if (!$icon instanceof NullIcon) {
                $resource_identification = $this->irss->manage()->find($icon->getRid());
                if ($resource_identification !== null) {
                    return $path_custom_file_icon = $this->irss->consume()->src($resource_identification)->getSrc(
                        false
                    );
                }
            }
        }
        return $path_default_file_icon = ilUtil::getImagePath("icon_file.svg");
    }

    public function updateIcon(string $a_rid, bool $a_active, bool $a_is_default_icon, array $a_suffixes): Icon
    {
        $icon = new CustomIcon($a_rid, $a_active, $a_is_default_icon, $a_suffixes);
        // Delete the old suffix entries of the given icon first as they can not be identified by form input and therefore cannot be overwritten - only deleted and created anew
        $this->db->manipulateF(
            "DELETE FROM " . self::SUFFIX_TABLE_NAME . " WHERE " . self::ICON_RESOURCE_IDENTIFICATION . " = %s",
            ['text'],
            [$icon->getRid()]
        );
        foreach ($icon->getSuffixes() as $suffix) {
            $this->db->insert(
                self::SUFFIX_TABLE_NAME,
                [
                    self::ICON_RESOURCE_IDENTIFICATION => ['text', $icon->getRid()],
                    self::SUFFIX => ['text', $suffix],
                ]
            );
        }
        $this->db->update(
            self::ICON_TABLE_NAME,
            [
                self::ICON_ACTIVE => ['integer', $icon->isActive()],
                self::IS_DEFAULT_ICON => ['integer', $icon->isDefaultIcon()]
            ],
            [self::ICON_RESOURCE_IDENTIFICATION => ['text', $icon->getRid()]]
        );
        return $icon;
    }

    public function deleteIconByRid(string $a_rid): bool
    {
        $icon = $this->getIconByRid($a_rid);
        if (!$icon instanceof NullIcon) {
            $this->db->manipulateF(
                "DELETE FROM " . self::SUFFIX_TABLE_NAME . " WHERE " . self::ICON_RESOURCE_IDENTIFICATION . " = %s",
                ['text'],
                [$a_rid]
            );
            $this->db->manipulateF(
                "DELETE FROM " . self::ICON_TABLE_NAME . " WHERE " . self::ICON_RESOURCE_IDENTIFICATION . " = %s",
                ['text'],
                [$a_rid]
            );
            return true;
        }
        return false;
    }
}
