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
// TODO: Implement migration depending on JF decision
if ($ilDB->tableColumnExists('frm_data', 'top_date')) {
    
}

if ($ilDB->tableColumnExists('frm_data', 'top_update')) {

}
?>
<#4>
<?php
// TODO: Implement migration depending on JF decision
if ($ilDB->tableColumnExists('frm_drafts_history', 'draft_date')) {

}
?>
<#5>
<?php
// TODO: Implement migration depending on JF decision
if ($ilDB->tableColumnExists('frm_posts', 'pos_date')) {

}

if ($ilDB->tableColumnExists('frm_posts', 'pos_update')) {

}

if ($ilDB->tableColumnExists('frm_posts', 'pos_cens_date')) {

}

if ($ilDB->tableColumnExists('frm_posts', 'pos_activation_date')) {

}
?>
<#6>
<?php
// TODO: Implement migration depending on JF decision
if ($ilDB->tableColumnExists('frm_posts_deleted', 'deleted_date')) {

}

if ($ilDB->tableColumnExists('frm_posts_deleted', 'post_date')) {

}
?>
<#7>
<?php
// TODO: Implement migration depending on JF decision
if ($ilDB->tableColumnExists('frm_posts_drafts', 'post_date')) {

}

if ($ilDB->tableColumnExists('frm_posts_drafts', 'post_update')) {

}
?>
<#8>
<?php
// TODO: Implement migration depending on JF decision
if ($ilDB->tableColumnExists('frm_posts_tree', 'fpt_date')) {

}
?>
<#9>
<?php
// TODO: Implement migration depending on JF decision
if ($ilDB->tableColumnExists('frm_thread_access', 'access_old_ts')) {

}
?>
<#10>
<?php
// TODO: Implement migration depending on JF decision
if ($ilDB->tableColumnExists('frm_threads', 'thr_date')) {

}

if ($ilDB->tableColumnExists('frm_threads', 'thr_update')) {

}
?>