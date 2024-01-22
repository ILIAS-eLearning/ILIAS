<#1>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#2>
<?php
$set = $ilDB->queryF(
    "SELECT availability_id FROM pdfgen_renderer_avail " .
    " WHERE renderer = %s AND service = %s AND purpose = %s",
    ["text", "text", "text"],
    ["PhantomJS", "Survey", "Results"]
);
if (!$ilDB->fetchAssoc($set)) {
    $ilDB->insert("pdfgen_renderer_avail", [
        "availability_id" => ["integer", $ilDB->nextId('pdfgen_renderer_avail')],
        "renderer" => ["text", "PhantomJS"],
        "service" => ["text", "Survey"],
        "purpose" => ["text", "Results"]
    ]);
}
?>
<#3>
<?php
$set = $ilDB->queryF(
    "SELECT availability_id FROM pdfgen_renderer_avail " .
    " WHERE renderer = %s AND service = %s AND purpose = %s",
    ["text", "text", "text"],
    ["WkhtmlToPdf", "Survey", "Results"]
);
if (!$ilDB->fetchAssoc($set)) {
    $ilDB->insert("pdfgen_renderer_avail", [
        "availability_id" => ["integer", $ilDB->nextId('pdfgen_renderer_avail')],
        "renderer" => ["text", "WkhtmlToPdf"],
        "service" => ["text", "Survey"],
        "purpose" => ["text", "Results"]
    ]);
}
?>
<#4>
<?php
$set = $ilDB->queryF(
    "SELECT purpose_id FROM pdfgen_purposes " .
    " WHERE service = %s AND purpose = %s",
    ["text",  "text"],
    ["Survey", "Results"]
);
if (!$ilDB->fetchAssoc($set)) {
    $ilDB->insert("pdfgen_purposes", [
        "purpose_id" => ["integer", $ilDB->nextId('pdfgen_purposes')],
        "service" => ["text", "Survey"],
        "purpose" => ["text", "Results"]
    ]);
}
?>
<#5>
<?php
$ilDB->update(
    "pdfgen_renderer_avail",
    [
    "renderer" => ["text", "WkhtmlToPdf"]
],
    [    // where
        "renderer" => ["text", "PhantomJS"],
        "service" => ["text", "Wiki"],
    ]
);
?>
<#6>
<?php
$ilDB->update(
    "pdfgen_renderer_avail",
    [
    "renderer" => ["text", "WkhtmlToPdf"]
],
    [    // where
        "renderer" => ["text", "PhantomJS"],
        "service" => ["text", "Portfolio"]
    ]
);
?>
<#7>
<?php
$ilDB->manipulateF(
    "DELETE FROM pdfgen_renderer_avail WHERE " .
    " renderer = %s AND service = %s",
    ["text", "text"],
    ["PhantomJS", "Survey"]
);
?>
<#8>
<?php
$ilDB->update(
    "pdfgen_map",
    [
    "preferred" => ["text", "WkhtmlToPdf"],
    "selected" => ["text", "WkhtmlToPdf"]
],
    [    // where
        "service" => ["text", "Wiki"]
    ]
);
?>
<#9>
<?php
$ilDB->update(
    "pdfgen_map",
    [
    "preferred" => ["text", "WkhtmlToPdf"],
    "selected" => ["text", "WkhtmlToPdf"]
],
    [    // where
        "service" => ["text", "Portfolio"]
    ]
);
?>
<#10>
<?php
$set = $ilDB->queryF(
    "SELECT map_id FROM pdfgen_map " .
    " WHERE service = %s AND purpose = %s",
    ["text", "text"],
    ["Survey", "Results"]
);
if (!$ilDB->fetchAssoc($set)) {
    $ilDB->insert("pdfgen_map", [
        "map_id" => ["integer", $ilDB->nextId('pdfgen_map')],
        "preferred" => ["text", "WkhtmlToPdf"],
        "selected" => ["text", "WkhtmlToPdf"],
        "service" => ["text", "Survey"],
        "purpose" => ["text", "Results"]
    ]);
}
?>
<#11>
<?php
// deleted:wq
?>

<#12>
<?php
global $DIC;
$DIC->database()->modifyTableColumn("usr_data", "login", [
    "type" => \ilDBConstants::T_TEXT,
    "length" => 190,
    "notnull" => false,
    "fixed" => false
]);
?>
<#13>
<?php
if (!$ilDB->tableExists('adv_md_values_enum')) {
    $ilDB->createTable('adv_md_values_enum', [
        'obj_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'sub_type' => [
            'type' => 'text',
            'length' => 10,
            'notnull' => true,
            'default' => "-"
        ],
        'sub_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'field_id' => [
            'type' => 'integer',
            'length' => 4,
            'notnull' => true,
            'default' => 0
        ],
        'disabled' => [
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ],
        'value_index' => [
            'type' => ilDBConstants::T_TEXT,
            'length' => 16,
            'notnull' => true,
        ]
    ]);

    $ilDB->addPrimaryKey('adv_md_values_enum', array('obj_id', 'sub_type', 'sub_id', 'field_id', 'value_index'));
}
?>
<#14>
<?php

$query = 'select field_id, field_type, field_values from adv_mdf_definition ' .
    'where field_type = 1  or field_type = 8 ';
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $values = unserialize($row->field_values);
    if (!is_array($values)) {
        continue;
    }
    $options = $values;

    $query = 'select * from adv_md_values_text ' .
        'where field_id = ' . $ilDB->quote($row->field_id, ilDBConstants::T_INTEGER);
    $val_res = $ilDB->query($query);
    while ($val_row = $val_res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $query = 'select * from adv_md_values_enum ' .
            'where obj_id = ' . $ilDB->quote($val_row->obj_id, ilDBConstants::T_INTEGER) . ' ' .
            'and sub_id = ' . $ilDB->quote($val_row->sub_id, ilDBConstants::T_INTEGER) . ' ' .
            'and sub_type = ' . $ilDB->quote($val_row->sub_type, ilDBConstants::T_TEXT) . ' ' .
            'and field_id = ' . $ilDB->quote($val_row->field_id, ilDBConstants::T_INTEGER);
        $exists_res = $ilDB->query($query);
        if ($exists_res->numRows()) {
            //ilLoggerFactory::getLogger('root')->info('field_id: ' . $val_row->field_id . ' is already migrated');
            continue;
        }
        $current_values = [];
        if (strpos($val_row->value, '~|~') === 0) {
            // multi enum
            $current_values = explode('~|~', $val_row->value);
            array_pop($current_values);
            array_shift($current_values);
        } else {
            $current_values[] = (string) $val_row->value;
        }
        //ilLoggerFactory::getLogger('root')->dump($current_values);
        $positions = [];
        foreach ($current_values as $value) {
            if (!strlen(trim($value))) {
                continue;
            }
            $idx = array_search($value, $options);
            if ($idx === false) {
                continue;
            }
            $positions[] = $idx;
        }

        //ilLoggerFactory::getLogger('root')->dump($positions);
        foreach ($positions as $pos) {
            $query = 'insert into adv_md_values_enum (obj_id, sub_type, sub_id, field_id, value_index, disabled) ' .
                'values ( ' .
                $ilDB->quote($val_row->obj_id, ilDBConstants::T_INTEGER) . ', ' .
                $ilDB->quote($val_row->sub_type, ilDBConstants::T_TEXT) . ', ' .
                $ilDB->quote($val_row->sub_id, ilDBConstants::T_INTEGER) . ', ' .
                $ilDB->quote($val_row->field_id, ilDBConstants::T_INTEGER) . ', ' .
                $ilDB->quote($pos, ilDBConstants::T_INTEGER) . ', ' .
                $ilDB->quote($val_row->disabled, ilDBConstants::T_INTEGER)
                . ' ) ';
            $ilDB->query($query);
        }
    }
}
?>
<#15>
<?php

if (!$ilDB->tableExists('adv_mdf_enum')) {
    $ilDB->createTable('adv_mdf_enum', [
        'field_id' => [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 4,
            'notnull' => true,
        ],
        'lang_code' => [
            'type' => ilDBConstants::T_TEXT,
            'notnull' => true,
            'length' => 5
        ],
        'idx' => [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 4,
            'notnull' => true,
        ],
        'value' => [
            'type' => ilDBConstants::T_TEXT,
            'length' => 4000,
            'notnull' => true
        ]
    ]);
    $ilDB->addPrimaryKey('adv_mdf_enum', array('field_id', 'lang_code', 'idx'));
}
?>
<#16>
<?php

$query = 'select value from settings where  module = ' . $ilDB->quote('common', ilDBConstants::T_TEXT) . ' ' .
    'and keyword = ' . $ilDB->quote('language', ilDBConstants::T_TEXT);
$res = $ilDB->query($query);
$default = 'en';
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $default = $row->value;
}
$query = 'update adv_md_record set lang_default = ' . $ilDB->quote($default, ilDBConstants::T_TEXT) . ' ' .
    'where lang_default IS NULL';
$ilDB->query($query);
?>

<#17>
<?php
$query = 'select * from adv_md_record ';
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = 'select * from adv_md_record_int ' .
        'where record_id = ' . $ilDB->quote($row->record_id, ilDBConstants::T_INTEGER) . ' ' .
        'and lang_code = ' . $ilDB->quote($row->lang_default, ilDBConstants::T_TEXT);
    $int_res = $ilDB->query($query);
    if ($int_res->numRows()) {
        continue;
    }
    $query = 'insert into adv_md_record_int (record_id, title, description, lang_code ) ' .
        'values ( ' .
        $ilDB->quote($row->record_id, ilDBConstants::T_INTEGER) . ', ' .
        $ilDB->quote($row->title, ilDBConstants::T_TEXT) . ', ' .
        $ilDB->quote($row->description, ilDBConstants::T_TEXT) . ', ' .
        $ilDB->quote($row->lang_default, ilDBConstants::T_TEXT) .
        ')' ;
    $ilDB->manipulate($query);
}
?>

<#18>
<?php
$query = 'select advf.field_id, lang_default, advf.title, advf.description from adv_mdf_definition advf ' .
    'join adv_md_record advr on advf.record_id = advr.record_id ';
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = 'select * from adv_md_field_int ' .
        'where field_id = ' . $ilDB->quote($row->field_id, ilDBConstants::T_INTEGER) . ' ' .
        'and lang_code = ' . $ilDB->quote($row->lang_default, ilDBConstants::T_TEXT);
    $int_res = $ilDB->query($query);
    if ($int_res->numRows()) {
        continue;
    }
    $query = 'insert into adv_md_field_int (field_id, title, description, lang_code ) ' .
        'values ( ' .
        $ilDB->quote($row->field_id, ilDBConstants::T_INTEGER) . ', ' .
        $ilDB->quote($row->title, ilDBConstants::T_TEXT) . ', ' .
        $ilDB->quote($row->description, ilDBConstants::T_TEXT) . ', ' .
        $ilDB->quote($row->lang_default, ilDBConstants::T_TEXT) .
        ')' ;
    $ilDB->manipulate($query);
}
?>

<#19>
<?php

$query = 'select advf.record_id, field_id, field_values, lang_default from adv_mdf_definition advf ' .
    'join adv_md_record advr on advf.record_id = advr.record_id ' . ' ' .
    'where ( field_type = ' . $ilDB->quote(1, ilDBConstants::T_INTEGER) . ' or ' .
    'field_type = ' . $ilDB->quote(8, ilDBConstants::T_INTEGER) . ' ) ';

$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $values = unserialize($row->field_values);
    if (array_key_exists('options', $values)) {
        $idx = 0;
        foreach ($values['options'] as $option) {
            $query = 'insert into adv_mdf_enum (field_id, lang_code, idx, value ) ' .
                'values ( ' .
                $ilDB->quote($row->field_id, ilDBConstants::T_INTEGER) . ', ' .
                $ilDB->quote($row->lang_default, ilDBConstants::T_TEXT) . ', ' .
                $ilDB->quote($idx++, ilDBConstants::T_INTEGER) . ', ' .
                $ilDB->quote($option, ilDBConstants::T_TEXT) .
                ' ) ';
            $ilDB->manipulate($query);
        }
    }
    if (array_key_exists('option_translations', $values)) {
        foreach ($values['option_translations'] as $lang => $options) {
            if ($lang == $row->lang_default) {
                continue;
            }
            $idx = 0;
            foreach ($options as $option) {
                $query = 'insert into adv_mdf_enum (field_id, lang_code, idx, value ) ' .
                    'values ( ' .
                    $ilDB->quote($row->field_id, ilDBConstants::T_INTEGER) . ', ' .
                    $ilDB->quote($lang, ilDBConstants::T_TEXT) . ', ' .
                    $ilDB->quote($idx++, ilDBConstants::T_INTEGER) . ', ' .
                    $ilDB->quote($option, ilDBConstants::T_TEXT) .
                    ' ) ';
                try {
                    $ilDB->manipulate($query);
                } catch (\Excpetion $e) {
                    ;
                }
            }
        }
    }
    if (
        !array_key_exists('options', $values) &&
        !array_key_exists('options_translations', $values) &&
        is_array($values)
    ) {
        $idx = 0;
        foreach ($values as $option) {
            $query = 'insert into adv_mdf_enum (field_id, lang_code, idx, value ) ' .
                'values ( ' .
                $ilDB->quote($row->field_id, ilDBConstants::T_INTEGER) . ', ' .
                $ilDB->quote($row->lang_default, ilDBConstants::T_TEXT) . ', ' .
                $ilDB->quote($idx++, ilDBConstants::T_INTEGER) . ', ' .
                $ilDB->quote($option, ilDBConstants::T_TEXT) .
                ' ) ';
            $ilDB->manipulate($query);
        }
    }
}
?>
<#20>
<?php
if (!$ilDB->tableColumnExists('adv_md_values_ltext', 'disabled')) {
    $ilDB->addTableColumn(
        'adv_md_values_ltext',
        'disabled',
        [
            'type' => ilDBConstants::T_INTEGER,
            'notnull' => true,
            'length' => 1,
            'default' => 0
        ]
    );
}
?>

<#21>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#22>
<?php
if ($ilDB->tableColumnExists("reg_access_limit", "limit_relative_y")) {
    $res = $ilDB->query("SELECT role_id, limit_relative_m, limit_relative_y FROM reg_access_limit WHERE limit_relative_y IS NOT NULL");
    $updateStatement = $ilDB->prepareManip("UPDATE reg_access_limit SET limit_relative_m = ? WHERE role_id = ?", ['months', 'role_id']);
    while ($row = $ilDB->fetchAssoc($res)) {
        $row['limit_relative_m'] = ($row['limit_relative_y'] * 12) + $row['limit_relative_m'];
        $ilDB->execute($updateStatement, [$row['limit_relative_m'], $row['role_id']]);
    }

    $ilDB->dropTableColumn("reg_access_limit", "limit_relative_y");
}
?>
<#23>
<?php
$table_name = 'il_adn_notifications';
$columns = [
    'event_start',
    'event_end',
    'display_start',
    'display_end',
    'create_date',
    'last_update',
];

foreach ($columns as $column) {
    if ($ilDB->tableExists($table_name)) {
        if ($ilDB->tableColumnExists($table_name, $column)) {
            $ilDB->dropTableColumn($table_name, $column);
        }
        $ilDB->addTableColumn($table_name, $column, array(
            "type" => "integer",
            "notnull" => false,
            "length" => 8,
            "default" => 0
        ));
    }
}
?>
<#24>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#25>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#26>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#27>
<?php
$ilDB->manipulate('delete from log_components where component_id = ' . $ilDB->quote('btsk', ilDBConstants::T_TEXT));
?>
<#28>
<?php
if (!$ilDB->tableColumnExists('cmix_users', 'privacy_ident')) {
    $ilDB->addTableColumn('cmix_users', 'privacy_ident', array(
        'type' => 'integer',
        'length' => 2,
        'notnull' => true,
        'default' => 0
    ));
    $ilDB->dropPrimaryKey('cmix_users');
    $ilDB->addPrimaryKey('cmix_users', array('obj_id', 'usr_id', 'privacy_ident'));
}
if (!$ilDB->tableColumnExists('cmix_settings', 'privacy_ident')) {
    $ilDB->addTableColumn('cmix_settings', 'privacy_ident', array(
        'type' => 'integer',
        'length' => 2,
        'notnull' => true,
        'default' => 0
    ));
}
if (!$ilDB->tableColumnExists('cmix_settings', 'privacy_name')) {
    $ilDB->addTableColumn('cmix_settings', 'privacy_name', array(
        'type' => 'integer',
        'length' => 2,
        'notnull' => true,
        'default' => 0
    ));
}
if (!$ilDB->tableColumnExists('lti_ext_provider', 'privacy_ident')) {
    $ilDB->addTableColumn('lti_ext_provider', 'privacy_ident', array(
        'type' => 'integer',
        'length' => 2,
        'notnull' => true,
        'default' => 0
    ));
}
if (!$ilDB->tableColumnExists('lti_ext_provider', 'privacy_name')) {
    $ilDB->addTableColumn('lti_ext_provider', 'privacy_name', array(
        'type' => 'integer',
        'length' => 2,
        'notnull' => true,
        'default' => 0
    ));
}
?>
<#29>
<?php
if ($ilDB->tableColumnExists('cmix_settings', 'user_ident')) {
    $set = $ilDB->query("SELECT obj_id, user_ident, user_name FROM cmix_settings");
    while ($row = $ilDB->fetchAssoc($set)) {
        $ident = 0;
        $name = 0;
        if ($row['user_ident'] == 'il_uuid_ext_account') {
            $ident = 1;
        }
        if ($row['user_ident'] == 'il_uuid_login') {
            $ident = 2;
        }
        if ($row['user_ident'] == 'real_email') {
            $ident = 3;
        }
        if ($row['user_ident'] == 'il_uuid_random') {
            $ident = 4;
        }
        if ($row['user_name'] == 'firstname') {
            $name = 1;
        }
        if ($row['user_name'] == 'lastname') {
            $name = 2;
        }
        if ($row['user_name'] == 'fullname') {
            $name = 3;
        }

        $ilDB->update(
            "cmix_users",
            [
                "privacy_ident" => ["integer", $ident]
            ],
            [	// where
                "obj_id" => ["integer", $row['obj_id']]
            ]
        );
        $ilDB->update(
            "cmix_settings",
            [
                "privacy_ident" => ["integer", $ident],
                "privacy_name" => ["integer", $name]
            ],
            [	// where
                "obj_id" => ["integer", $row['obj_id']]
            ]
        );
    }
}
?>
<#30>
<?php
if ($ilDB->tableColumnExists('lti_ext_provider', 'user_ident')) {
    $set = $ilDB->query("SELECT id, user_ident, user_name FROM lti_ext_provider");
    while ($row = $ilDB->fetchAssoc($set)) {
        $ident = 0;
        $name = 0;
        if ($row['user_ident'] == 'il_uuid_ext_account') {
            $ident = 1;
        }
        if ($row['user_ident'] == 'il_uuid_login') {
            $ident = 2;
        }
        if ($row['user_ident'] == 'real_email') {
            $ident = 3;
        }
        if ($row['user_ident'] == 'il_uuid_random') {
            $ident = 4;
        }
        if ($row['user_name'] == 'firstname') {
            $name = 1;
        }
        if ($row['user_name'] == 'lastname') {
            $name = 2;
        }
        if ($row['user_name'] == 'fullname') {
            $name = 3;
        }

        $ilDB->update(
            "lti_ext_provider",
            [
                "privacy_ident" => ["integer", $ident],
                "privacy_name" => ["integer", $name]
            ],
            [	// where
                "id" => ["integer", $row['id']]
            ]
        );
    }
}
?>
<#31>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#32>
<?php
if (!$ilDB->tableColumnExists('cmix_lrs_types', 'privacy_ident')) {
    $ilDB->addTableColumn('cmix_lrs_types', 'privacy_ident', array(
        'type' => 'integer',
        'length' => 2,
        'notnull' => true,
        'default' => 0
    ));
}
if (!$ilDB->tableColumnExists('cmix_lrs_types', 'privacy_name')) {
    $ilDB->addTableColumn('cmix_lrs_types', 'privacy_name', array(
        'type' => 'integer',
        'length' => 2,
        'notnull' => true,
        'default' => 0
    ));
}
if ($ilDB->tableColumnExists('cmix_lrs_types', 'user_ident')) {
    $set = $ilDB->query("SELECT type_id, user_ident, user_name FROM cmix_lrs_types");
    while ($row = $ilDB->fetchAssoc($set)) {
        $ident = 0;
        $name = 0;
        if ($row['user_ident'] == 'il_uuid_ext_account') {
            $ident = 1;
        }
        if ($row['user_ident'] == 'il_uuid_login') {
            $ident = 2;
        }
        if ($row['user_ident'] == 'real_email') {
            $ident = 3;
        }
        if ($row['user_ident'] == 'il_uuid_random') {
            $ident = 4;
        }
        if ($row['user_name'] == 'firstname') {
            $name = 1;
        }
        if ($row['user_name'] == 'lastname') {
            $name = 2;
        }
        if ($row['user_name'] == 'fullname') {
            $name = 3;
        }

        $ilDB->update(
            "cmix_lrs_types",
            [
                "privacy_ident" => ["integer", $ident],
                "privacy_name" => ["integer", $name]
            ],
            [	// where
                "type_id" => ["integer", $row['type_id']]
            ]
        );
    }
}
?>
<#33>
<?php
if ($ilDB->tableColumnExists('cmix_lrs_types', 'user_ident')) {
    $ilDB->dropTableColumn("cmix_lrs_types", "user_ident");
    $ilDB->dropTableColumn("cmix_lrs_types", "user_name");
}
if ($ilDB->tableColumnExists('cmix_settings', 'user_ident')) {
    $ilDB->dropTableColumn("cmix_settings", "user_ident");
    $ilDB->dropTableColumn("cmix_settings", "user_name");
}
if ($ilDB->tableColumnExists('lti_ext_provider', 'user_ident')) {
    $ilDB->dropTableColumn("lti_ext_provider", "user_ident");
    $ilDB->dropTableColumn("lti_ext_provider", "user_name");
}
?>
<#34>
<?php
$ilDB->replace(
    'settings',
    [
        'module' => ['text', 'adve'],
        'keyword' => ['text', 'autosave']
    ],
    [
        'value' => ['text', '30']
    ]
);
?>
<#35>
<?php
$query = 'select value from settings where  module = ' . $ilDB->quote('common', ilDBConstants::T_TEXT) . ' ' .
    'and keyword = ' . $ilDB->quote('language', ilDBConstants::T_TEXT);
$res = $ilDB->query($query);
$default = 'en';
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $default = $row->value;
}
$query = 'update adv_md_record set lang_default = ' . $ilDB->quote($default, ilDBConstants::T_TEXT) . ' ' .
    'where lang_default = ' . $ilDB->quote('', ilDBConstants::T_TEXT);
$ilDB->manipulate($query);

// update md_record_int
$query = 'select record_id from adv_md_record_int where lang_code = ' . $ilDB->quote('', ilDBConstants::T_TEXT);
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = 'select record_id from adv_md_record_int where lang_code = ' . $ilDB->quote($default, ilDBConstants::T_TEXT) . ' ' .
        'and record_id = ' . $ilDB->quote($row->record_id, ilDBConstants::T_INTEGER);
    $setres = $ilDB->query($query);
    if ($setres->numRows()) {
        $query = 'delete from adv_md_record_int where lang_code = ' . $ilDB->quote('', ilDBConstants::T_TEXT) . ' ' .
            'and record_id = ' . $ilDB->quote($row->record_id, ilDBConstants::T_INTEGER);
        $ilDB->manipulate($query);
    }
}
$query = 'update adv_md_record_int set lang_code = ' . $ilDB->quote($default, ilDBConstants::T_TEXT) . ' ' .
    'where lang_code = ' . $ilDB->quote('', ilDBConstants::T_TEXT);
$ilDB->manipulate($query);

// update md_field_int
$query = 'select field_id from adv_md_field_int where lang_code = ' . $ilDB->quote('', ilDBConstants::T_TEXT);
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = 'select field_id from adv_md_field_int where lang_code = ' . $ilDB->quote($default, ilDBConstants::T_TEXT) . ' ' .
        'and field_id = ' . $ilDB->quote($row->field_id, ilDBConstants::T_INTEGER);
    $setres = $ilDB->query($query);
    if ($setres->numRows()) {
        $query = 'delete from adv_md_field_int where lang_code = ' . $ilDB->quote('', ilDBConstants::T_TEXT) . ' ' .
            'and field_id = ' . $ilDB->quote($row->field_id, ilDBConstants::T_INTEGER);
        $ilDB->manipulate($query);
    }
}
$query = 'update adv_md_field_int set lang_code = ' . $ilDB->quote($default, ilDBConstants::T_TEXT) . ' ' .
    'where lang_code = ' . $ilDB->quote('', ilDBConstants::T_TEXT);
$ilDB->manipulate($query);

// update adv_mdf_enum
$query = 'select field_id, lang_code, idx from adv_mdf_enum ' .
    'where lang_code = ' . $ilDB->quote('', ilDBConstants::T_TEXT);
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $query = 'select field_id, lang_code, idx from  adv_mdf_enum where lang_code = ' . $ilDB->quote($default, ilDBConstants::T_TEXT) . ' ' .
        'and field_id = ' . $ilDB->quote($row->field_id, ilDBConstants::T_INTEGER) . ' ' .
        'and idx = ' . $ilDB->quote($row->idx, ilDBConstants::T_INTEGER);
    $setres = $ilDB->query($query);
    if ($setres->numRows()) {
        $query = 'delete from  adv_mdf_enum where lang_code = ' . $ilDB->quote('', ilDBConstants::T_TEXT) . ' ' .
            'and field_id = ' . $ilDB->quote($row->field_id, ilDBConstants::T_INTEGER) . ' ' .
            'and idx = ' . $ilDB->quote($row->idx, ilDBConstants::T_INTEGER);
        $ilDB->manipulate($query);
    }
}
$query = 'update adv_mdf_enum set lang_code = ' . $ilDB->quote($default, ilDBConstants::T_TEXT) . ' ' .
    'where lang_code = ' . $ilDB->quote('', ilDBConstants::T_TEXT);
$ilDB->manipulate($query);
?>
<#36>
<?php
if (!$ilDB->tableColumnExists('ldap_server_settings', 'escape_dn')) {
    $ilDB->addTableColumn(
        'ldap_server_settings',
        'escape_dn',
        [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 1,
            'notnull' => true,
            'default' => 0
        ]
    );
}
?>
<#37>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#38>
<?php
if (!$ilDB->indexExistsByFields('exc_returned', array('filetitle'))) {
    $ilDB->addIndex('exc_returned', array('filetitle'), 'i3');
}
?>
<#39>
<?php
if ($ilDB->uniqueConstraintExists('cmi_gobjective', array('user_id','objective_id','scope_id'))) {
    $ilDB->dropUniqueConstraintByFields('cmi_gobjective', array('user_id','objective_id','scope_id'));
}
$query = "show index from cmi_gobjective where Key_name = 'PRIMARY'";
$res = $ilDB->query($query);
if (!$ilDB->numRows($res)) {
    $ilDB->addPrimaryKey('cmi_gobjective', array('user_id', 'scope_id', 'objective_id'));
}
?>
<#40>
<?php
if ($ilDB->uniqueConstraintExists('cp_suspend', array('user_id','obj_id'))) {
    $ilDB->dropUniqueConstraintByFields('cp_suspend', array('user_id','obj_id'));
}
$query = "show index from cp_suspend where Key_name = 'PRIMARY'";
$res = $ilDB->query($query);
if (!$ilDB->numRows($res)) {
    $ilDB->addPrimaryKey('cp_suspend', array('user_id', 'obj_id'));
}
?>
<#41>
<?php
$read_learning_progress = 0;
$read_outcomes = 0;
$res = $ilDB->queryF(
    "SELECT ops_id FROM rbac_operations WHERE operation = %s",
    array('text'),
    array('read_learning_progress')
);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $read_learning_progress = $row->ops_id;
}
$res = $ilDB->queryF(
    "SELECT ops_id FROM rbac_operations WHERE operation = %s",
    array('text'),
    array('read_outcomes')
);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $read_outcomes = $row->ops_id;
}
if ($read_outcomes > 0 && $read_learning_progress > 0) {
    $res = $ilDB->queryF(
        "SELECT rol_id, parent, type FROM rbac_templates WHERE (type=%s OR type=%s) AND ops_id=%s",
        array('text', 'text', 'integer'),
        array('cmix', 'lti', $read_learning_progress)
    );
    while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
        $resnum = $ilDB->queryF(
            "SELECT rol_id FROM rbac_templates WHERE rol_id = %s AND type = %s AND ops_id = %s AND parent = %s",
            array('integer', 'text', 'integer', 'integer'),
            array($row->rol_id, $row->type, $read_outcomes, $row->parent)
        );
        if (!$ilDB->numRows($resnum)) {
            $ilDB->insert('rbac_templates', array(
                    'rol_id' => array('integer', $row->rol_id),
                    'type' => array('text', $row->type),
                    'ops_id' => array('integer', $read_outcomes),
                    'parent' => array('integer', $row->parent)
                ));
        }
    }
}
?>
<#42>
<?php
$ilDB->update(
    "rbac_operations",
    [
    "op_order" => ["integer", 3900]
],
    [    // where
        "operation" => ["text", "redact"]
    ]
);
?>
<#43>
<?php
if (!$ilDB->indexExistsByFields('booking_reservation', array('date_from'))) {
    $ilDB->addIndex('booking_reservation', array('date_from'), 'i3');
}
?>
<#44>
<?php
if (!$ilDB->indexExistsByFields('booking_reservation', array('date_to'))) {
    $ilDB->addIndex('booking_reservation', array('date_to'), 'i4');
}
?>
<#45>
<?php
$query = "show index from il_meta_oer_stat where Key_name = 'PRIMARY'";
$res = $ilDB->query($query);
if (!$ilDB->numRows($res)) {
    $ilDB->addPrimaryKey('il_meta_oer_stat', ['obj_id']);
}
?>
<#46>
<?php
if (!$ilDB->tableColumnExists('il_bt_value', 'position')) {
    $ilDB->addTableColumn(
        'il_bt_value',
        'position',
        [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 8,
            'notnull' => true,
            'default' => 0
        ]
    );
}
?>
<#47>
<?php
if (!$ilDB->indexExistsByFields('il_bt_value', array('bucket_id'))) {
    $ilDB->addIndex(
        'il_bt_value',
        array('bucket_id'),
        'i1'
    );
}
if (!$ilDB->indexExistsByFields('il_bt_value_to_task', array('task_id'))) {
    $ilDB->addIndex(
        'il_bt_value_to_task',
        array('task_id'),
        'i1'
    );
}
if (!$ilDB->indexExistsByFields('il_bt_value_to_task', array('value_id'))) {
    $ilDB->addIndex(
        'il_bt_value_to_task',
        array('value_id'),
        'i2'
    );
}
?>
<#48>
<?php
if (!$ilDB->tableColumnExists('il_bt_value_to_task', 'position')) {
    $ilDB->addTableColumn(
        'il_bt_value_to_task',
        'position',
        [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 8,
            'notnull' => true,
            'default' => 0
        ]
    );
}
?>
<#49>
<?php
if (!$ilDB->indexExistsByFields('il_resource_revision', array('identification'))) {
    $ilDB->addIndex(
        'il_resource_revision',
        array('identification'),
        'i1'
    );
}
if (!$ilDB->indexExistsByFields('il_resource_stakeh', array('identification'))) {
    $ilDB->addIndex(
        'il_resource_stakeh',
        array('identification'),
        'i1'
    );
}
if (!$ilDB->indexExistsByFields('il_resource_stakeh', array('stakeholder_id'))) {
    $ilDB->addIndex(
        'il_resource_stakeh',
        array('stakeholder_id'),
        'i2'
    );
}
if (!$ilDB->indexExistsByFields('il_resource_info', array('identification'))) {
    $ilDB->addIndex(
        'il_resource_info',
        array('identification'),
        'i1'
    );
}
if (!$ilDB->indexExistsByFields('il_resource', array('storage_id'))) {
    $ilDB->addIndex(
        'il_resource',
        array('storage_id'),
        'i1'
    );
}
?>

<#50>
<?php
if (!$ilDB->tableColumnExists("prg_usr_progress", "individual")) {
    $ilDB->addTableColumn("prg_usr_progress", "individual", [
            "type" => "integer",
            "length" => 1,
            "notnull" => true,
            "default" => 0
    ]);

    $ilDB->manipulate("UPDATE prg_usr_progress SET individual = 1 WHERE last_change_by IS NOT NULL");
}
?>

<#51>
<?php
$old = "risky_to_fail_mail_send";
$new = "sent_mail_risky_to_fail";
$table = "prg_usr_progress";
if ($ilDB->tableColumnExists($table, $old) && !$ilDB->tableColumnExists($table, $new)) {
    $ilDB->renameTableColumn($table, $old, $new);
}
?>

<#52>
<?php
if (!$ilDB->tableColumnExists("prg_usr_progress", "sent_mail_expires")) {
    $ilDB->addTableColumn("prg_usr_progress", "sent_mail_expires", [
            "type" => "timestamp",
            "notnull" => false
    ]);
}
?>

<#53>
<?php
$ilDB->manipulate(
    "UPDATE il_cert_cron_queue SET adapter_class = " . $ilDB->quote('ilTestPlaceholderValues', 'text') . " WHERE adapter_class = " . $ilDB->quote('ilTestPlaceHolderValues', 'text')
);
$ilDB->manipulate(
    "UPDATE il_cert_cron_queue SET adapter_class = " . $ilDB->quote('ilExercisePlaceholderValues', 'text') . " WHERE adapter_class = " . $ilDB->quote('ilExercisePlaceHolderValues', 'text')
);
?>
<#54>
<?php
if (!$ilDB->tableColumnExists('tst_rnd_quest_set_qpls', 'pool_ref_id')) {
    $ilDB->addTableColumn(
        'tst_rnd_quest_set_qpls',
        'pool_ref_id',
        [
            'type' => ilDBConstants::T_INTEGER,
            'length' => 8,
            'notnull' => false,
            'default' => null
        ]
    );
}
?>
<#55>
<?php
if (!$ilDB->tableColumnExists('cmix_users', 'registration')) {
    $ilDB->addTableColumn('cmix_users', 'registration', array(
        'type' => 'text',
        'length' => 255,
        'notnull' => true,
        'default' => ''
    ));
}
if (!$ilDB->tableColumnExists('cmix_settings', 'publisher_id')) {
    $ilDB->addTableColumn('cmix_settings', 'publisher_id', array(
        'type' => 'text',
        'length' => 255,
        'notnull' => true,
        'default' => ''
    ));
}
if (!$ilDB->tableColumnExists('cmix_settings', 'anonymous_homepage')) {
    $ilDB->addTableColumn('cmix_settings', 'anonymous_homepage', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
    ));
}
?>
<#56>
<?php
if (!$ilDB->tableColumnExists('cmix_settings', 'moveon')) {
    $ilDB->addTableColumn('cmix_settings', 'moveon', array(
        'type' => 'text',
        'length' => 32,
        'notnull' => true,
        'default' => ''
    ));
}
?>
<#57>
<?php
if (!$ilDB->tableColumnExists('cmix_token', 'cmi5_session')) {
    $ilDB->addTableColumn("cmix_token", "cmi5_session", [
        'type' => 'text',
        'length' => 255,
        'notnull' => true,
        'default' => ''
    ]);
}
?>
<#58>
<?php
if (!$ilDB->tableColumnExists('cmix_token', 'returned_for_cmi5_session')) {
    $ilDB->addTableColumn("cmix_token", "returned_for_cmi5_session", [
        'type' => 'text',
        'length' => 255,
        'notnull' => true,
        'default' => ''
    ]);
}
?>
<#59>
<?php
if (!$ilDB->tableColumnExists('cmix_settings', 'launch_parameters')) {
    $ilDB->addTableColumn('cmix_settings', 'launch_parameters', array(
        'type' => 'text',
        'length' => 255,
        'notnull' => true,
        'default' => ''
    ));
}
?>
<#60>
<?php
if (!$ilDB->tableColumnExists('cmix_settings', 'entitlement_key')) {
    $ilDB->addTableColumn('cmix_settings', 'entitlement_key', array(
        'type' => 'text',
        'length' => 255,
        'notnull' => true,
        'default' => ''
    ));
}
?>
<#61>
<?php
if (!$ilDB->tableColumnExists('cmix_token', 'cmi5_session_data')) {
    $ilDB->addTableColumn("cmix_token", "cmi5_session_data", [
        'type' => 'clob'
    ]);
}
?>
<#62>
<?php
if (!$ilDB->tableColumnExists('cmix_users', 'satisfied')) {
    $ilDB->addTableColumn('cmix_users', 'satisfied', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 0
    ));
}
?>
<#63>
<?php
if (!$ilDB->tableColumnExists('cmix_settings', 'switch_to_review')) {
    $ilDB->addTableColumn('cmix_settings', 'switch_to_review', array(
            'type' => 'integer',
            'length' => 1,
            'notnull' => true,
            'default' => 1
    ));
}
?>
<#64>
<?php
/** @var $ilDB ilDBInterface */
if (!$ilDB->tableExists('il_resource_stkh_u') && $ilDB->tableExists('il_resource_stakeh')) {
    $ilDB->renameTable('il_resource_stakeh', 'il_resource_stkh_u');
    $ilDB->createTable(
        'il_resource_stkh',
        [
            'id' => ['type' => 'text', 'length' => 32, 'notnull' => true, 'default' => ''],
            'class_name' => ['type' => 'text', 'length' => 250, 'notnull' => true, 'default' => ''],
        ]
    );
    $ilDB->addPrimaryKey('il_resource_stkh', ['id']);
    $ilDB->manipulate("INSERT INTO il_resource_stkh (id, class_name) SELECT DISTINCT stakeholder_id, stakeholder_class FROM il_resource_stkh_u;");
}

if ($ilDB->tableColumnExists('il_resource_stkh_u', 'stakeholder_class')) {
    $ilDB->dropTableColumn('il_resource_stkh_u', 'stakeholder_class');
}
if ($ilDB->tableColumnExists('il_resource_stkh_u', 'internal')) {
    $ilDB->dropTableColumn('il_resource_stkh_u', 'internal');
}
?>
<#65>
<?php
//  rename all identification columns to rid
/** @var $ilDB ilDBInterface */
if (!$ilDB->tableColumnExists('il_resource', 'rid')) {
    $ilDB->renameTableColumn(
        'il_resource',
        'identification',
        'rid'
    );
}
if (!$ilDB->tableColumnExists('il_resource_info', 'rid')) {
    $ilDB->renameTableColumn(
        'il_resource_info',
        'identification',
        'rid'
    );
}
if (!$ilDB->tableColumnExists('il_resource_revision', 'rid')) {
    $ilDB->renameTableColumn(
        'il_resource_revision',
        'identification',
        'rid'
    );
}
if (!$ilDB->tableColumnExists('il_resource_stkh_u', 'rid')) {
    $ilDB->renameTableColumn(
        'il_resource_stkh_u',
        'identification',
        'rid'
    );
}
?>
<#66>
<?php
// set all rid columns to the same size
$attributes = [
    'length' => 64,
    'notnull' => true,
    'default' => '',
];
$ilDB->modifyTableColumn(
    'il_resource',
    'rid',
    $attributes
);
$ilDB->modifyTableColumn(
    'il_resource_info',
    'rid',
    $attributes
);
$ilDB->modifyTableColumn(
    'il_resource_revision',
    'rid',
    $attributes
);
$ilDB->modifyTableColumn(
    'il_resource_stkh_u',
    'rid',
    $attributes
);
$ilDB->modifyTableColumn(
    'file_data',
    'rid',
    $attributes
);
?>
<#67>
<?php
if (!$ilDB->tableColumnExists('il_resource_info', 'version_number')) {
    $ilDB->addTableColumn(
        'il_resource_info',
        'version_number',
        [
            'type' => 'integer',
            'length' => 8
        ]
    );

    $ilDB->manipulate("UPDATE il_resource_info
JOIN il_resource_revision ON il_resource_info.internal = il_resource_revision.internal
SET il_resource_info.version_number = il_resource_revision.version_number
");
}
?>
<#68>
<?php
// remove internal columns and add primaries
if ($ilDB->tableColumnExists('il_resource_revision', 'internal')) {
    $ilDB->dropTableColumn('il_resource_revision', 'internal');
    $ilDB->addPrimaryKey(
        'il_resource_revision',
        [
            'rid',
            'version_number',
        ]
    );
}
if ($ilDB->tableColumnExists('il_resource_info', 'internal')) {
    $ilDB->dropTableColumn('il_resource_info', 'internal');
    $ilDB->addPrimaryKey(
        'il_resource_info',
        [
            'rid',
            'version_number',
        ]
    );
}
if ($ilDB->tableColumnExists('il_resource_stkh', 'internal')) {
    $ilDB->dropTableColumn('il_resource_stkh', 'internal');
    $ilDB->addPrimaryKey(
        'il_resource_stkh',
        [
            'rid',
            'stakeholder_id',
        ]
    );
}
?>
<#69>
<?php
// set several fields to notnull
$attributes = [
    'notnull' => true,
    'default' => '',
];
$table_fields = [
    'il_resource' => ['storage_id'],
    'il_resource_info' => ['title', 'size', 'creation_date'],
    'il_resource_revision' => ['owner_id', 'title'],
];
foreach ($table_fields as $table => $fields) {
    foreach ($fields as $field) {
        $ilDB->modifyTableColumn(
            $table,
            $field,
            $attributes
        );
    }
}
?>
<#70>
<?php
// add index to file_data rid
if (!$ilDB->indexExistsByFields('file_data', ['rid'])) {
    $ilDB->addIndex('file_data', ['rid'], 'i1');
}

?>
<#71>
<?php
// several changes to irss tables
$ilDB->modifyTableColumn(
    'il_resource_revision',
    'available',
    [
        'default' => 1,
    ]
);
$ilDB->modifyTableColumn(
    'il_resource_stkh_u',
    'stakeholder_id',
    ['length' => 64]
);
$ilDB->modifyTableColumn(
    'il_resource_stkh',
    'id',
    ['length' => 64]
);
$ilDB->modifyTableColumn(
    'il_resource_info',
    'title',
    ['length' => 255]
);
$ilDB->modifyTableColumn(
    'il_resource_revision',
    'title',
    ['length' => 255]
);


?>
<#72>
<?php
if (!$ilDB->indexExistsByFields('il_bt_bucket', ['user_id'])) {
    $ilDB->addIndex('il_bt_bucket', ['user_id'], 'i1');
}
?>
<#73>
<?php
$ilDB->replace(
    "settings",
    [        // pk
                           "module" => ["text", "common"],
                           "keyword" => ["text", "rep_favourites"],
],
    [
        "value" => ["text", "1"]
    ]
);

?>
<#74>
<?php
if (!$ilDB->indexExistsByFields('booking_reservation', ['context_obj_id'])) {
    $ilDB->addIndex('booking_reservation', ['context_obj_id'], 'i5');
}
?>
<#75>
<?php
if (!$ilDB->indexExistsByFields('booking_schedule', ['pool_id'])) {
    $ilDB->addIndex('booking_schedule', ['pool_id'], 'i1');
}
?>
<#76>
<?php
if (!$ilDB->indexExistsByFields('booking_object', ['schedule_id'])) {
    $ilDB->addIndex('booking_object', ['schedule_id'], 'i2');
}
?>
<#77>
<?php
if (!$ilDB->indexExistsByFields('exc_assignment', ['exc_id'])) {
    $ilDB->addIndex('exc_assignment', ['exc_id'], 'i1');
}
?>
<#78>
<?php
if (!$ilDB->indexExistsByFields('exc_members', ['usr_id'])) {
    $ilDB->addIndex('exc_members', ['usr_id'], 'i1');
}
?>
<#79>
<?php
if (!$ilDB->indexExistsByFields('exc_assignment', ['deadline_mode', 'exc_id'])) {
    $ilDB->addIndex('exc_assignment', ['deadline_mode', 'exc_id'], 'i2');
}
?>
<#80>
<?php
if (!$ilDB->indexExistsByFields('exc_ass_file_order', ['assignment_id'])) {
    $ilDB->addIndex('exc_ass_file_order', ['assignment_id'], 'i1');
}
?>
<#81>
<?php
if (!$ilDB->indexExistsByFields('il_exc_team', ['id'])) {
    $ilDB->addIndex('il_exc_team', ['id'], 'i1');
}
?>
<#82>
<?php
$fields = ['gap_id'];
if (!$ilDB->indexExistsByFields('qpl_a_cloze', $fields)) {
    $ilDB->addIndex(
        'qpl_a_cloze',
        $fields,
        'i2'
    );
}

$fields = ['gap_fi', 'question_fi'];
if (!$ilDB->indexExistsByFields('qpl_a_cloze_combi_res', $fields)) {
    $ilDB->addIndex(
        'qpl_a_cloze_combi_res',
        $fields,
        'i1'
    );
}
?>
<#83>
<?php
$ilDB->manipulateF('DELETE FROM settings WHERE keyword = %s', ['text'], ['enable_block_moving']);
$ilDB->manipulate('DELETE FROM il_block_setting WHERE ' . $ilDB->like('type', 'text', 'pd%'));
?>
<#84>
<?php
if ($ilDB->tableColumnExists('adv_mdf_definition', 'field_values')) {
    $field_infos = [
        'type' => 'clob',
        'notnull' => false,
        'default' => null
    ];
    $ilDB->modifyTableColumn('adv_mdf_definition', 'field_values', $field_infos);
}
?>
<#85>
<?php
// fixes https://mantis.ilias.de/view.php?id=32226: due to moving the favourites item
// to a new provider, the GS will create a new entry once the main-menu administration
// is entered. Therefore, if the hotfix can only be applied if the new entry wasn't
// already created, otherwise we cannot tell if the user adapted the new item to work
// around the bug. In this case we can just delete the old favourites' entry.

if ($ilDB->tableExists('il_mm_items')) {
    $result = $ilDB->fetchAll(
        $ilDB->queryF(
            "SELECT COUNT(identification) AS amount FROM il_mm_items WHERE identification = %s;",
            ['text'],
            ['ILIAS\\Repository\\Provider\\RepositoryMainBarProvider|mm_pd_sel_items']
        )
    );

    if (0 === (int) $result[0]['amount']) {
        $new_values = [
            'identification' => ['text', 'ILIAS\\Repository\\Provider\\RepositoryMainBarProvider|mm_pd_sel_items'],
        ];

        $ilDB->update('il_mm_items', $new_values, [
            'identification' => ['text', 'ILIAS\\PersonalDesktop\\PDMainBarProvider|mm_pd_sel_items'],
        ]);
    } else {
        $ilDB->manipulateF(
            "DELETE FROM il_mm_items WHERE identification = %s",
            ['text'],
            ['ILIAS\\PersonalDesktop\\PDMainBarProvider|mm_pd_sel_items']
        );
    }
}
?>
<#86>
<?php
if (!$ilDB->tableColumnExists('pg_amd_page_list', 'sdata')) {
    $field_infos = [
        'type' => 'clob',
        'notnull' => false,
        'default' => null
    ];
    $ilDB->addTableColumn('pg_amd_page_list', 'sdata', $field_infos);
}
?>
<#87>
<?php

function migrate($id, $field_id, $data) : void
{
    global $ilDB;

    $query = 'UPDATE pg_amd_page_list ' .
        'SET sdata = ' . $ilDB->quote(serialize(serialize($data)), ilDBConstants::T_TEXT) . ' ' .
        'WHERE id = ' . $ilDB->quote($id, ilDBConstants::T_INTEGER) . ' ' .
        'AND field_id = ' . $ilDB->quote($field_id, ilDBConstants::T_INTEGER);
    $ilDB->manipulate($query);
}

function migrateData($field_id, $data) : array
{
    global $ilDB;

    if (!is_array($data)) {
        return [];
    }
    $indexes = [];
    foreach ($data as $idx => $value) {
        $query = 'SELECT idx from adv_mdf_enum ' .
            'WHERE value = ' . $ilDB->quote($value, ilDBConstants::T_TEXT) . ' ' .
            'AND field_id = ' . $ilDB->quote($field_id, ilDBConstants::T_INTEGER);
        $res = $ilDB->query($query);

        $found_index = false;
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $indexes[] = (int) $row->idx;
            $found_index = true;
        }
        if ($found_index) {
            continue;
        }
        $query = 'SELECT idx from adv_mdf_enum ' .
            'WHERE idx = ' . $ilDB->quote($value, ilDBConstants::T_TEXT) . ' ' .
            'AND field_id = ' . $ilDB->quote($field_id, ilDBConstants::T_INTEGER);
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $indexes[] = (int) $row->idx;
        }
    }
    return $indexes;
}

$query = 'SELECT id, pg.field_id, data, field_type FROM pg_amd_page_list pg ' .
    'JOIN adv_mdf_definition adv ' .
    'ON pg.field_id = adv.field_id ' .
    'WHERE sdata IS null ';
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    if ($row->field_type == 1 || $row->field_type == 8) {
        migrate(
            $row->id,
            $row->field_id,
            migrateData(
                $row->field_id,
                unserialize(unserialize($row->data))
            )
        );
    } else {
        migrate(
            $row->id,
            $row->field_id,
            unserialize(unserialize($row->data))
        );
    }
}
?>
<#88>
<?php
$ilDB->manipulateF('DELETE FROM desktop_item WHERE item_id = %s', ['integer'], [1]);
$ilDB->manipulateF('DELETE FROM rep_rec_content_role WHERE ref_id = %s', ['integer'], [1]);
?>
<#89>
<?php
if (!$ilDB->indexExistsByFields('qpl_questions', array('owner'))) {
    $ilDB->addIndex('qpl_questions', array('owner'), 'i5');
}
if (!$ilDB->indexExistsByFields('qpl_num_range', array('question_fi'))) {
    $ilDB->addIndex('qpl_num_range', array('question_fi'), 'i6');
}
?>
<#90>
<?php
// Add new index
if (!$ilDB->indexExistsByFields('style_template', ['style_id'])) {
    $ilDB->addIndex('style_template', ['style_id'], 'i1');
}
?>
<#91>
<?php
//avoid notices for $ilDB->uniqueConstraintExists and dropUniqueConstraintByFields
try {
    $ilDB->dropUniqueConstraint('cmix_token', 'c1');
} catch (\Exception $e) {
}
$ilDB->addUniqueConstraint('cmix_token', array('obj_id', 'usr_id', 'ref_id'), 'c1');
?>
<#92>
<?php
// fix #34521
$check = "SELECT * FROM settings WHERE module = 'MathJax' AND keyword = 'enable' AND VALUE = '1'";
$result = $ilDB->query($check);
if ($row = $ilDB->fetchAssoc($result)) {
    // don't change the url of an activated mathjax
} else {
    // change the default value
    $old = 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=TeX-AMS-MML_HTMLorMML';
    $new = 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.9/MathJax.js?config=TeX-AMS-MML_HTMLorMML,Safe';

    $ilDB->manipulateF(
        "UPDATE settings SET value=%s WHERE module='MathJax' AND keyword='path_to_mathjax' AND value=%s",
        array('text','text'),
        array($new, $old)
    );
}
?>
<#93>
<?php
// change default URL as recommended here: https://www.mathjax.org/MathJax-v2-7-9-available/
$check = "SELECT * FROM settings WHERE module = 'MathJax' AND keyword = 'enable' AND VALUE = '1'";
$result = $ilDB->query($check);
if ($row = $ilDB->fetchAssoc($result)) {
    // don't change the url of an activated mathjax
} else {
    // change the default value
    $old = 'https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.9/MathJax.js?config=TeX-AMS-MML_HTMLorMML,Safe';
    $new = 'https://cdn.jsdelivr.net/npm/mathjax@2.7.9/MathJax.js?config=TeX-AMS-MML_HTMLorMML,Safe';

    $ilDB->manipulateF(
        "UPDATE settings SET value=%s WHERE module='MathJax' AND keyword='path_to_mathjax' AND value=%s",
        array('text','text'),
        array($new, $old)
    );
}
?>
<#94>
<?php
global $DIC;
$DIC->database()->modifyTableColumn("object_translation", "title", [
    "type" => \ilDBConstants::T_TEXT,
    "length" => 255,
    "notnull" => false
]);
?>
<#95>
<?php
if (!$ilDB->indexExistsByFields('il_dcl_field_prop', array('id', 'field_id'))) {
    $ilDB->addIndex('il_dcl_field_prop', array('id', 'field_id'), 'i1');
}
if (!$ilDB->indexExistsByFields('il_dcl_tview_set', array('tableview_id'))) {
    $ilDB->addIndex('il_dcl_tview_set', array('tableview_id'), 'i1');
}
?>
<#96>
<?php
if (!$ilDB->indexExistsByFields('style_usage', array('style_id'))) {
    $ilDB->addIndex('style_usage', array('style_id'), 'i1');
}
?>
<#97>
<?php
$ilDB->manipulateF('DELETE FROM cmix_users WHERE usr_id = %s', ['integer'], [13]);
?>
<#98>
<?php
if (!$ilDB->indexExistsByFields('webr_items', array('webr_id'))) {
    $ilDB->addIndex('webr_items', array('webr_id'), 'i3');
}
?>
<#99>
<?php
if (!$ilDB->indexExistsByFields('cal_entries', array('starta'))) {
    $ilDB->addIndex('cal_entries', array('starta'), 'i3');
}
if (!$ilDB->indexExistsByFields('cal_entries', array('enda'))) {
    $ilDB->addIndex('cal_entries', array('enda'), 'i4');
}
?>
<#100>
<?php
if (!$ilDB->indexExistsByFields('event_appointment', array('event_id'))) {
    $ilDB->addIndex('event_appointment', array('event_id'), 'i1');
}
?>
<#101>
<?php
if (!$ilDB->indexExistsByFields('rbac_fa', ['assign', 'rol_id'])) {
    $ilDB->addIndex('rbac_fa', ['assign', 'rol_id'], 'i2');
}
if (!$ilDB->indexExistsByFields('rbac_fa', ['assign', 'parent'])) {
    $ilDB->addIndex('rbac_fa', ['assign', 'parent'], 'i3');
}
?>
<#102>
<?php
$ilDB->modifyTableColumn(
    'usr_session',
    'session_id',
    [
        'type' => ilDBConstants::T_TEXT,
        'length' => '256'
    ]
);
$ilDB->modifyTableColumn(
    'usr_session_stats_raw',
    'session_id',
    [
        'type' => ilDBConstants::T_TEXT,
        'length' => '256'
    ]
);
try {
    $ilDB->modifyTableColumn(
        'usr_sess_istorage',
        'session_id',
        [
            'type' => ilDBConstants::T_TEXT,
            'length' => '256'
        ]
    );
} catch (\Exception $e) {
    $message = "DB Hotfix 102: \n\n"
        . "We could not Update the length of the column `session_id` in the table\n"
        . "`usr_session_istorage` as the table engine is MyIsam.\n"
        . "This step will be finished after updating to ILIAS 8. You could also change\n"
        . "the ENGINE manually to InnoDB, if you require longer session_ids.";
    global $ilLog;
    $ilLog->warning($message);
}
?>
<#103>
<?php
// Add index
if ($this->db->indexExistsByFields('tree', ['child'])) {
    $this->db->dropIndex('tree', 'i1');
}
?>
<#104>
<?php
if ($ilDB->tableExists('adv_md_values_text') &&
    $ilDB->tableExists('adv_md_values_ltext')
) {
    // inserts all values from adv_md_values_text into adv_md_values_ltext WITHOUT
    // adv_md_values_ltext.value_index, ignoring duplicate entries.
    $ilDB->manipulate("
        INSERT IGNORE INTO adv_md_values_ltext (field_id, obj_id, `value`, value_index, disabled, sub_type, sub_id)
            SELECT val.field_id, val.obj_id, val.value, '', val.disabled, val.sub_type, val.sub_id
                FROM adv_md_values_text AS val
        ;
    ");

    // inserts all values from adv_md_values_text into adv_md_values_ltext WITH
    // adv_md_values_ltext.value_index, whereas the value_index will be the default
    // lang-code of adv_md_field_int because the old table didn't store this information.
    $ilDB->manipulate("
        INSERT IGNORE INTO adv_md_values_ltext (field_id, obj_id, `value`, value_index, disabled, sub_type, sub_id)
            SELECT val.field_id, val.obj_id, val.value, field.lang_code, val.disabled, val.sub_type, val.sub_id
                FROM adv_md_values_text AS val
                JOIN adv_md_field_int AS field ON field.field_id = val.field_id
        ;
    ");
}
?>
<#105>
<?php
    $ilDB->manipulate("DELETE FROM rbac_operations WHERE operation='create_dbk'");
?>
<#106>
<?php
    if (!$ilDB->tableExists('usr_change_email_token')) {
        $ilDB->createTable(
            'usr_change_email_token',
            [
                'token' => [
                    'type' => 'text',
                    'length' => 32
                ],
                'new_email' => [
                    'type' => 'text',
                    'length' => 256
                ],
                'valid_until' => [
                    'type' => 'integer',
                    'length' => 8
                ]
            ]
        );
    }
?>
<#107>
<?php
if (
    $this->db->tableExists('il_cert_template')
    && !$this->db->indexExistsByFields('il_cert_template', ['background_image_path', 'currently_active'])
) {
    $this->db->addIndex('il_cert_template', ['background_image_path', 'currently_active'], 'i5');
}

if (
    $this->db->tableExists('il_cert_user_cert')
    && !$this->db->indexExistsByFields('il_cert_user_cert', ['background_image_path', 'currently_active'])
) {
    $this->db->addIndex('il_cert_user_cert', ['background_image_path', 'currently_active'], 'i7');
}
?>
<#108>
<?php
    if (!$this->db->indexExistsByFields('loc_settings', ['itest'])) {
        $this->db->addIndex('loc_settings', ['itest'], 'i1');
    }
    if (!$this->db->indexExistsByFields('loc_settings', ['qtest'])) {
        $this->db->addIndex('loc_settings', ['qtest'], 'i2');
    }
?>
<#109>
<?php
if (
        $ilDB->tableExists('webr_items') &&
        !$ilDB->indexExistsByFields('webr_items', ['webr_id', 'active'])
) {
    $ilDB->addIndex('webr_items', ['webr_id', 'active'], 'i4');
}
?>
<#110>
<?php
    if ($ilDB->tableColumnExists('usr_data', 'street')) {
        $ilDB->modifyTableColumn('usr_data', 'street', [
            'type' => \ilDBConstants::T_TEXT,
            'length' => 512,
            'notnull' => false
        ]);
    }
?>

