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

abstract class ilDclFieldTypePlugin extends ilPlugin
{
    public const DB_TYPES = ['text', 'text', 'integer', 'date'];
    public const COMPONENT_NAME = "DataCollection";
    public const SLOT_ID = "dclfth";
    public const PLUGIN_SLOT_PREFIX = 'plugin_fth_';

    public function install(): void
    {
        $field_type_name = ilDclFieldTypePlugin::PLUGIN_SLOT_PREFIX . $this->getId();
        $datatypes = ilDclDatatype::getAllDatatype(true);
        foreach ($datatypes as $datatype) {
            if ($datatype->getTitle() === $field_type_name) {
                parent::install();
                return;
            }
        }

        $field_model_class = 'il' . $this->getPluginName() . 'FieldModel';
        $type = (new $field_model_class())->getStorageLocationOverride() ?? $this->getStorageLocation();
        $this->db->manipulateF(
            'INSERT INTO il_dcl_datatype (id, title, ildb_type, storage_location, sort) SELECT GREATEST(MAX(id), 1000) + 1, %s, %s, %s, GREATEST(MAX(sort), 10000) + 10 FROM il_dcl_datatype;',
            [
                ilDBConstants::T_TEXT,
                ilDBConstants::T_TEXT,
                ilDBConstants::T_INTEGER,
            ],
            [
                $field_type_name,
                $this::DB_TYPES[$type],
                $type
            ]
        );
    }

    public function getStorageLocation(): int
    {
        return 1;
    }

    public static function getDataType(string $plugin_id): string
    {
        return self::PLUGIN_SLOT_PREFIX . $plugin_id;
    }

    public static function getPluginId(string $datatype): string
    {
        if (self::isPluginDatatype($datatype)) {
            return substr($datatype, strlen(self::PLUGIN_SLOT_PREFIX));
        }
        throw new ilPluginException('Invalid datatype prefix for FieldTypHook-plugin');
    }

    public static function isPluginDatatype(string $datatype): bool
    {
        return substr($datatype, 0, strlen(self::PLUGIN_SLOT_PREFIX)) === self::PLUGIN_SLOT_PREFIX;
    }
}
