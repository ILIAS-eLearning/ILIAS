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
if (!$ilDB->tableColumnExists('mail', 'send_datetime')) {
    $ilDB->addTableColumn(
        'mail',
        'send_datetime',
        [
            'type' => 'timestamp',
            'notnull' => true,
        ]
    );
    $ilDB->manipulate('UPDATE mail SET send_datetime = send_time');
}

if ($ilDB->tableColumnExists('mail', 'send_time')) {
    $ilDB->manipulate('UPDATE mail SET send_time = NULL');

    $field = [
        'type' => 'integer',
        'length' => 8,
        'notnull' => true,
        'default' => 0,
    ];

    $ilDB->modifyTableColumn('mail', 'send_time', $field);
    $ilDB->manipulate('UPDATE mail SET send_time = UNIX_TIMESTAMP(send_datetime)');
}
?>