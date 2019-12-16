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