<#1>
<?php
if (!$ilDB->tableColumnExists("exc_ass_reminders", "last_send_day")) {
    $field = array(
        'type'    => 'date',
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
$path =  'Services/PDFGeneration/classes/renderer/wkhtmltopdf/class.ilWkhtmlToPdfRenderer.php';
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
