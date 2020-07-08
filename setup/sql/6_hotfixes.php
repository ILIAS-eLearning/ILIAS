<#1>
<?php
// Patch for https://mantis.ilias.de/view.php?id=28550
$ilDB->update("settings", ["keyword" => [ilDBConstants::T_TEXT, "db_hotfixes_6"]], ["module" => [ilDBConstants::T_TEXT, "common"], "keyword" => [ilDBConstants::T_TEXT, "db_hotfixes_6_0"]]);
$hostfix_version = $ilDB->queryF('SELECT value from settings WHERE module=%s AND keyword=%s', [ilDBConstants::T_TEXT, ilDBConstants::T_TEXT], ["common", "db_hotfixes_6"])->fetchAssoc()["value"];
if (!empty($hostfix_version)) {
    $nr = $hostfix_version;
}

if (!$ilDB->tableColumnExists("exc_ass_reminders", "last_send_day")) {
    $field = array(
        'type' => 'date',
        'notnull' => false,
    );
    $ilDB->addTableColumn("exc_ass_reminders", "last_send_day", $field);
}
?>
<#2>
<?php
$set = $ilDB->queryF(
    "SELECT * FROM exc_ass_reminders " .
    " WHERE last_send > %s ",
    ["integer"],
    [0]
);
while ($rec = $ilDB->fetchAssoc($set)) {
    $last_send_day = date("Y-m-d", $rec["last_send"]);
    $ilDB->update(
        "exc_ass_reminders",
        [
        "last_send_day" => ["date", $last_send_day]
    ],
        [    // where
            "ass_id" => ["integer", $rec["ass_id"]],
            "last_send" => ["integer", $rec["last_send"]]
        ]
    );
}
?>
<#3>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#4>
<?php
$ilDB->manipulateF(
    'DELETE FROM ctrl_classfile WHERE class = %s',
    ['text'],
    ['ilwkhtmltopdfconfigformgui']
);
?>
<#5>
<?php
$ilDB->manipulateF(
    'DELETE FROM pdfgen_renderer WHERE renderer = %s',
    ['text'],
    ['WkhtmlToPdf']
);
?>
<#6>
<?php
$ilDB->manipulateF(
    'DELETE FROM pdfgen_renderer_avail WHERE renderer = %s',
    ['text'],
    ['WkhtmlToPdf']
);
?>
<#7>
<?php
require_once './Services/PDFGeneration/classes/class.ilPDFCompInstaller.php';
$renderer = 'WkhtmlToPdf';
$path = 'Services/PDFGeneration/classes/renderer/wkhtmltopdf/class.ilWkhtmlToPdfRenderer.php';
ilPDFCompInstaller::registerRenderer($renderer, $path);
$service = 'Test';
$purpose = 'UserResult'; // According to name given. Call multiple times.
ilPDFCompInstaller::registerRendererAvailability($renderer, $service, $purpose);

$purpose = 'PrintViewOfQuestions'; // According to name given. Call multiple times.
ilPDFCompInstaller::registerRendererAvailability($renderer, $service, $purpose);
?>
<#8>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#9>
<?php
$query = 'select obd.obj_id from object_data obd left join crs_reference_settings crs on obd.obj_id = crs.obj_id  ' .
    'where crs.obj_id IS NULL and type = ' . $ilDB->quote('crsr', \ilDBConstants::T_TEXT);
$res = $ilDB->query($query);
while ($row = $res->fetchRow(\ilDBConstants::FETCHMODE_OBJECT)) {
    $query = 'insert into crs_reference_settings (obj_id, member_update ) values (' .
        $ilDB->quote($row->obj_id, \ilDBConstants::T_INTEGER) . ', ' .
        $ilDB->quote(0, \ilDBConstants::T_INTEGER) .
        ')';
    $ilDB->manipulate($query);
}
?>
<#10>
<?php
$set = $ilDB->queryF(
    "SELECT * FROM object_description ",
    [],
    []
);
while ($rec = $ilDB->fetchAssoc($set)) {
    if ($rec["description"] != "") {
        $ilDB->update(
            "object_translation",
            [
            "description" => ["text", $rec["description"]]
        ],
            [    // where
                "obj_id" => ["integer", $rec["obj_id"]],
                "lang_default" => ["integer", 1]
            ]
        );
    }
}
?>
<#11>
<?php
if ($ilDB->tableColumnExists('prg_settings', 'access_ctrl_org_pos')) {
    $ilDB->dropTableColumn('prg_settings', 'access_ctrl_org_pos');
}
?>
<#12>
<?php
$ilCtrlStructureReader->getStructure();
?>
<#13>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#14>
<?php
global $DIC;
$ilDB = $DIC['ilDB'];

if ($ilDB->tableColumnExists('iass_members', 'record')) {
    $field_infos = [
        'type' => 'clob',
        'notnull' => false,
        'default' => ''
    ];
    $ilDB->modifyTableColumn('iass_members', 'record', $field_infos);
}

if ($ilDB->tableColumnExists('iass_members', 'internal_note')) {
    $field_infos = [
        'type' => 'clob',
        'notnull' => false,
        'default' => ''
    ];
    $ilDB->modifyTableColumn('iass_members', 'internal_note', $field_infos);
}

if ($ilDB->tableColumnExists('iass_settings', 'content')) {
    $field_infos = [
        'type' => 'clob',
        'notnull' => false,
        'default' => ''
    ];
    $ilDB->modifyTableColumn('iass_settings', 'content', $field_infos);
}

if ($ilDB->tableColumnExists('iass_settings', 'record_template')) {
    $field_infos = [
        'type' => 'clob',
        'notnull' => false,
        'default' => ''
    ];
    $ilDB->modifyTableColumn('iass_settings', 'record_template', $field_infos);
}

if ($ilDB->tableColumnExists('iass_info_settings', 'mails')) {
    $field_infos = [
        'type' => 'clob',
        'notnull' => false,
        'default' => ''
    ];
    $ilDB->modifyTableColumn('iass_info_settings', 'mails', $field_infos);
}
?>

<#15>
<?php
    $ilCtrlStructureReader->getStructure();
?>

<#16>
<?php
include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
ilDBUpdateNewObjectType::addAdminNode('lsos', 'LearningSequenceAdmin');
?>
<#17>
<?php
$ilDB->manipulate(
    "UPDATE il_cert_cron_queue SET adapter_class = " . $ilDB->quote('ilTestPlaceholderValues', 'text') . " WHERE adapter_class = " . $ilDB->quote('ilTestPlaceHolderValues', 'text')
);
$ilDB->manipulate(
    "UPDATE il_cert_cron_queue SET adapter_class = " . $ilDB->quote('ilExercisePlaceholderValues', 'text') . " WHERE adapter_class = " . $ilDB->quote('ilExercisePlaceHolderValues', 'text')
);
?>
<#18>
<?php
//template for global role il_lti_user

include_once './Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
ilDBUpdateNewObjectType::addRBACTemplate(
    'root',
    'il_lti_user',
    'LTI user template for global role',
    [
        ilDBUpdateNewObjectType::getCustomRBACOperationId('read')
    ]
);
$new_tpl_id = 0;
$query = 'SELECT obj_id FROM object_data WHERE title = ' . $ilDB->quote('il_lti_user', 'text');
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $new_tpl_id = $row->obj_id;
}
if ($new_tpl_id > 0) {
    $ilDB->manipulateF(
        "INSERT INTO rbac_templates (rol_id, type, ops_id, parent)" .
        " VALUES (%s, %s, %s, %s)",
        array("integer", "text", "integer", "integer"),
        array($new_tpl_id, 'cat', ilDBUpdateNewObjectType::getCustomRBACOperationId('read'), 8)
    );
}

// local role
ilDBUpdateNewObjectType::addRBACTemplate(
    'sahs',
    'il_lti_learner',
    'LTI learner template for local role',
    [
        ilDBUpdateNewObjectType::getCustomRBACOperationId('visible'),
        ilDBUpdateNewObjectType::getCustomRBACOperationId('read')
    ]
);
?>
<#19>
<?php
include_once './Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
$new_tpl_id = 0;
$query = 'SELECT obj_id FROM object_data WHERE title = ' . $ilDB->quote('il_lti_learner', 'text');
$res = $ilDB->query($query);
while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
    $new_tpl_id = $row->obj_id;
}
if ($new_tpl_id > 0) {
    $ilDB->manipulateF(
        "INSERT INTO rbac_templates (rol_id, type, ops_id, parent)" .
        " VALUES (%s, %s, %s, %s)",
        array("integer", "text", "integer", "integer"),
        array($new_tpl_id, 'tst', ilDBUpdateNewObjectType::getCustomRBACOperationId('visible'), 8)
    );
    $ilDB->manipulateF(
        "INSERT INTO rbac_templates (rol_id, type, ops_id, parent)" .
        " VALUES (%s, %s, %s, %s)",
        array("integer", "text", "integer", "integer"),
        array($new_tpl_id, 'tst', ilDBUpdateNewObjectType::getCustomRBACOperationId('read'), 8)
    );
    $ilDB->manipulateF(
        "INSERT INTO rbac_templates (rol_id, type, ops_id, parent)" .
        " VALUES (%s, %s, %s, %s)",
        array("integer", "text", "integer", "integer"),
        array($new_tpl_id, 'lm', ilDBUpdateNewObjectType::getCustomRBACOperationId('visible'), 8)
    );
    $ilDB->manipulateF(
        "INSERT INTO rbac_templates (rol_id, type, ops_id, parent)" .
        " VALUES (%s, %s, %s, %s)",
        array("integer", "text", "integer", "integer"),
        array($new_tpl_id, 'lm', ilDBUpdateNewObjectType::getCustomRBACOperationId('read'), 8)
    );
}

?>
<#20>
<?php
$setting = new ilSetting();
$idx = $setting->get('ilfrmreadidx1', 0);
if (!$idx) {
    $ilDB->addIndex('frm_user_read', ['usr_id', 'post_id'], 'i1');
    $setting->set('ilfrmreadidx1', 1);
}
?>

<#21>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#22>
<?php
require_once './Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
$type = 'prgr';
$ops_id = ilDBUpdateNewObjectType::RBAC_OP_READ;
$type_id = ilDBUpdateNewObjectType::getObjectTypeId($type);
if (ilDBUpdateNewObjectType::isRBACOperation($type_id, $ops_id)) {
    ilDBUpdateNewObjectType::deleteRBACOperation($type, $ops_id);
}
?>

<#23>
<?php
$ilCtrlStructureReader->getStructure();
?>

<#24>
<?php
/** @var $ilDB ilDBInterface */
$ilDB->manipulateF("DELETE FROM cron_job WHERE job_id  = %s", ['text'], ['bgtsk_gc']);
?>
