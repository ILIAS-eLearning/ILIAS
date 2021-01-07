<#1>
<?php
    $ilCtrlStructureReader->getStructure();
?>
<#2>
<?php
$set = $ilDB->queryF("SELECT availability_id FROM pdfgen_renderer_avail " .
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
$set = $ilDB->queryF("SELECT availability_id FROM pdfgen_renderer_avail " .
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
$set = $ilDB->queryF("SELECT purpose_id FROM pdfgen_purposes " .
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
$ilDB->update("pdfgen_renderer_avail", [
    "renderer" => ["text", "WkhtmlToPdf"]
], [    // where
        "renderer" => ["text", "PhantomJS"],
        "service" => ["text", "Wiki"],
    ]
);
?>
<#6>
<?php
$ilDB->update("pdfgen_renderer_avail", [
    "renderer" => ["text", "WkhtmlToPdf"]
], [    // where
        "renderer" => ["text", "PhantomJS"],
        "service" => ["text", "Portfolio"]
    ]
);
?>
<#7>
<?php
$ilDB->manipulateF("DELETE FROM pdfgen_renderer_avail WHERE " .
    " renderer = %s AND service = %s",
    ["text", "text"],
    ["PhantomJS", "Survey"]
);
?>
<#8>
<?php
$ilDB->update("pdfgen_map", [
    "preferred" => ["text", "WkhtmlToPdf"],
    "selected" => ["text", "WkhtmlToPdf"]
], [    // where
        "service" => ["text", "Wiki"]
    ]
);
?>
<#9>
<?php
$ilDB->update("pdfgen_map", [
    "preferred" => ["text", "WkhtmlToPdf"],
    "selected" => ["text", "WkhtmlToPdf"]
], [    // where
        "service" => ["text", "Portfolio"]
    ]
);
?>
<#10>
<?php
$set = $ilDB->queryF("SELECT map_id FROM pdfgen_map " .
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
global $DIC;
$DIC->database()->modifyTableColumn("usr_data", "login", [
    "type" => \ilDBConstants::T_TEXT,
    "length" => 255,
    "notnull" => false,
    "fixed" => false
]);
?>

