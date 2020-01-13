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
$set = $ilDB->queryF("SELECT * FROM exc_ass_reminders ".
    " WHERE last_send > %s ",
    ["integer"],
    [0]
);
while ($rec = $ilDB->fetchAssoc($set))
{
    $last_send_day = date("Y-m-d", $rec["last_send"]);
    $ilDB->update("exc_ass_reminders", [
        "last_send_day" => ["date", $last_send_day]
    ], [    // where
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
