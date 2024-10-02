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

class ilDataCollectionDBUpdateSteps9 implements ilDatabaseUpdateSteps
{
    protected ilDBInterface $db;

    public function prepare(ilDBInterface $db): void
    {
        $this->db = $db;
    }

    public function step_1(): void
    {
        $this->db->manipulate(
            "UPDATE il_dcl_tableview " .
            "SET description=" . $this->db->quote("", "text") .
            "WHERE description is null"
        );
        $this->db->modifyTableColumn("il_dcl_tableview", "description", [
            'notnull' => true,
            'default' => ''
        ]);
    }

    public function step_2(): void
    {
        $this->db->manipulate(
            "UPDATE il_dcl_tview_set " .
            "SET in_filter=0 " .
            "WHERE in_filter is null"
        );
        $this->db->manipulate(
            "UPDATE il_dcl_tview_set " .
            "SET visible=0 " .
            "WHERE visible is null"
        );
        $this->db->manipulate(
            "UPDATE il_dcl_tview_set " .
            "SET filter_changeable=0 " .
            "WHERE filter_changeable is null"
        );
        $this->db->modifyTableColumn("il_dcl_tview_set", "in_filter", [
            'notnull' => 1,
            'default' => 0
        ]);
        $this->db->modifyTableColumn("il_dcl_tview_set", "visible", [
            'notnull' => 1,
            'default' => 0
        ]);
        $this->db->modifyTableColumn("il_dcl_tview_set", "filter_changeable", [
            'notnull' => 1,
            'default' => 0
        ]);
    }

    public function step_3(): void
    {
        $this->db->manipulate(
            "UPDATE il_dcl_tfield_set " .
            "SET exportable=0 " .
            "WHERE exportable is null"
        );
        $this->db->modifyTableColumn("il_dcl_tfield_set", "exportable", [
            'notnull' => 1,
            'default' => 0
        ]);
    }

    public function step_4(): void
    {
        $this->db->modifyTableColumn("il_dcl_stloc3_value", "value", ['notnull' => false]);
    }

    public function step_5(): void
    {
        if (!$this->db->indexExistsByFields('il_dcl_field_prop', ['id', 'field_id'])) {
            $this->db->addIndex('il_dcl_field_prop', ['id', 'field_id'], 'i1');
        }
        if (!$this->db->indexExistsByFields('il_dcl_tview_set', ['tableview_id'])) {
            $this->db->addIndex('il_dcl_tview_set', ['tableview_id'], 'i1');
        }
    }

    public function step_6(): void
    {
        $this->db->insert("il_dcl_datatype", [
            'id' => ['integer', ilDclDatatype::INPUTFORMAT_FILE],
            'title' => ['text', 'file'],
            'ildb_type' => ['text', 'text'],
            'storage_location' => ['integer', 1], // string-storage location
            'sort' => ['integer', 75], // legacy + 5
        ]);
    }

    public function step_7(): void
    {
        $this->db->manipulateF(
            "DELETE FROM il_dcl_datatype WHERE id = %s",
            ['integer'],
            [defined(ilDclDatatype::class . '::INPUTFORMAT_FILEUPLOAD') ? ilDclDatatype::INPUTFORMAT_FILEUPLOAD : 6]
        );
    }

    public function step_8(): void
    {
        $this->db->modifyTableColumn(
            'il_dcl_sel_opts',
            'value',
            [
                "length" => 4000,
            ]
        );
    }

    public function step_9(): void
    {
        $this->db->manipulate(
            "UPDATE il_dcl_field_prop, il_dcl_field 
	                    SET name='link_detail_page_text'
                    WHERE name='link_detail_page'
	                AND il_dcl_field_prop.field_id=il_dcl_field.id
	                AND il_dcl_field.datatype_id=2"
        );
    }

    public function step_10(): void
    {
        if (!$this->db->indexExistsByFields('il_dcl_field_prop', array('field_id'))) {
            $this->db->addIndex('il_dcl_field_prop', array('field_id'), 'i2');
        }
        if (!$this->db->indexExistsByFields('il_dcl_sel_opts', array('field_id'))) {
            $this->db->addIndex('il_dcl_sel_opts', array('field_id'), 'i1');
        }
        if (!$this->db->indexExistsByFields('il_dcl_sel_opts', array('opt_id'))) {
            $this->db->addIndex('il_dcl_sel_opts', array('opt_id'), 'i2');
        }
        if (!$this->db->indexExistsByFields('il_dcl_tview_set', array('field'))) {
            $this->db->addIndex('il_dcl_tview_set', array('field'), 'i2');
        }
        if (!$this->db->indexExistsByFields('il_dcl_tview_set', array('in_filter'))) {
            $this->db->addIndex('il_dcl_tview_set', array('in_filter'), 'i3');
        }
        if (!$this->db->indexExistsByFields('il_dcl_tfield_set', array('field'))) {
            $this->db->addIndex('il_dcl_tfield_set', array('field'), 'i3');
        }
        if (!$this->db->indexExistsByFields('il_dcl_tfield_set', array('table_id'))) {
            $this->db->addIndex('il_dcl_tfield_set', array('table_id'), 'i4');
        }
    }

    public function step_11(): void
    {
        $this->db->manipulateF(
            'UPDATE il_dcl_field_prop prop INNER JOIN il_dcl_field field ON field.id = prop.field_id ' .
            'SET name = "link_detail_page_mob" WHERE field.datatype_id = %s AND name = "link_detail_page"',
            [ilDBConstants::T_INTEGER],
            [ilDclDatatype::INPUTFORMAT_MOB]
        );
    }

    public function step_12(): void
    {
        $this->db->manipulateF(
            'UPDATE il_dcl_stloc1_value v ' .
            'INNER JOIN il_dcl_record_field rf ON rf.id = v.record_field_id ' .
            'INNER JOIN il_dcl_field f ON f.id = rf.field_id ' .
            'SET v.value = REPLACE(v.value, "<br />", "\r\n") WHERE f.datatype_id = %s',
            [ilDBConstants::T_INTEGER],
            [ilDclDatatype::INPUTFORMAT_TEXT]
        );
    }

    public function step_13(): void
    {
        if ($this->db->tableColumnExists('il_dcl_tableview', 'step_vs')) {
            $this->db->dropTableColumn('il_dcl_tableview', 'step_vs');
        }
        if ($this->db->tableColumnExists('il_dcl_tableview', 'step_c')) {
            $this->db->dropTableColumn('il_dcl_tableview', 'step_c');
        }
        if ($this->db->tableColumnExists('il_dcl_tableview', 'step_e')) {
            $this->db->dropTableColumn('il_dcl_tableview', 'step_e');
        }
        if ($this->db->tableColumnExists('il_dcl_tableview', 'step_o')) {
            $this->db->dropTableColumn('il_dcl_tableview', 'step_o');
        }
        if ($this->db->tableColumnExists('il_dcl_tableview', 'step_s')) {
            $this->db->dropTableColumn('il_dcl_tableview', 'step_s');
        }
    }

    public function step_14(): void
    {
        $this->db->manipulate('UPDATE il_dcl_field_prop SET value = "" WHERE value IS NULL');
    }

    public function step_15(): void
    {
        if ($this->db->tableExists('il_dcl_field_prop_b')) {
            $this->db->dropTable('il_dcl_field_prop_b');
        }

        if ($this->db->tableExists('il_dcl_field_prop_s_b')) {
            $this->db->dropTable('il_dcl_field_prop_s_b');
        }
    }

    public function step_16(): void
    {
        global $DIC;
        $slot = $DIC['component.repository']->getPluginSlotById(ilDclFieldTypePlugin::SLOT_ID);
        foreach ($slot->getPlugins() as $plugin) {
            $plugin = $DIC['component.factory']->getPlugin($plugin->getId());
            $field_type_name = ilDclFieldTypePlugin::getDataType($plugin->getId());

            $field_ids = [];
            $stmt = $this->db->queryF(
                'SELECT field_id FROM il_dcl_field_prop WHERE name = "plugin_hook_name" AND value = %s',
                [ilDBConstants::T_TEXT],
                [$plugin->getPluginName()]
            );
            while ($row = $this->db->fetchAssoc($stmt)) {
                $field_ids[] = (int) $row['field_id'];
            }

            $id = 0;
            $stmt = $this->db->queryF('SELECT id FROM il_dcl_datatype WHERE title LIKE %s', [ilDBConstants::T_TEXT], [$field_type_name]);
            while ($row = $this->db->fetchAssoc($stmt)) {
                $id = (int) $row['id'];
            }
            if ($id === 0) {
                $type = $plugin->getStorageLocation();
                $field_model_class = 'il' . $plugin->getPluginName() . 'FieldModel';
                $type = (new $field_model_class())->getStorageLocationOverride() ?? $plugin->getStorageLocation();

                $this->db->manipulateF(
                    'INSERT INTO il_dcl_datatype (id, title, ildb_type, storage_location, sort) SELECT GREATEST(MAX(id), 1000) + 1, %s, %s, %s, GREATEST(MAX(sort), 10000) + 10 FROM il_dcl_datatype;',
                    [
                        ilDBConstants::T_TEXT,
                        ilDBConstants::T_TEXT,
                        ilDBConstants::T_INTEGER
                    ],
                    [
                        $field_type_name,
                        ilDclFieldTypePlugin::DB_TYPES[$type],
                        $type
                    ]
                );
                $stmt = $this->db->queryF('SELECT id FROM il_dcl_datatype WHERE title LIKE %s', [ilDBConstants::T_TEXT], [$field_type_name]);
                $id = (int) $this->db->fetchAssoc($stmt)['id'];
            }

            foreach ($field_ids as $field_id) {
                $this->db->manipulateF(
                    'UPDATE il_dcl_field SET datatype_id = %s WHERE id = %s',
                    [ilDBConstants::T_INTEGER, ilDBConstants::T_INTEGER],
                    [$id, $field_id]
                );
            }

            $this->db->manipulateF(
                'DELETE FROM il_dcl_field_prop WHERE name = "plugin_hook_name" AND value = %s',
                [ilDBConstants::T_TEXT],
                [$plugin->getPluginName()]
            );
        }
        $this->db->manipulateF(
            'DELETE FROM il_dcl_datatype WHERE id = %s',
            [ilDBConstants::T_TEXT],
            [ilDclDatatype::INPUTFORMAT_PLUGIN]
        );
    }
}
