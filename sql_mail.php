<#1>
<?php
// Delete before merge!!!!!!!!!!!


$signature = "\n\n* * * * *\n";
$signature .= "[CLIENT_NAME]\n";
$signature .= "[CLIENT_DESC]\n";
$signature .= "[CLIENT_URL]\n";

$ilSetting = new ilSetting();

$prevent_smtp_globally        = $ilSetting->get('prevent_smtp_globally', 0);
$mail_system_sender_name      = $ilSetting->get('mail_system_sender_name', '');
$mail_external_sender_noreply = $ilSetting->get('mail_external_sender_noreply', '');
$mail_system_return_path      = $ilSetting->get('mail_system_return_path', '');

$ilSetting->set('mail_allow_external', !(int)$prevent_smtp_globally);

$ilSetting->set('mail_system_usr_from_addr', $mail_external_sender_noreply);
$ilSetting->set('mail_system_usr_from_name', $mail_system_sender_name);
$ilSetting->set('mail_system_usr_env_from_addr', $mail_system_return_path);

$ilSetting->set('mail_system_sys_from_addr', $mail_external_sender_noreply);
$ilSetting->set('mail_system_sys_from_name', $mail_system_sender_name);
$ilSetting->set('mail_system_sys_reply_to_addr', $mail_external_sender_noreply);
$ilSetting->set('mail_system_sys_env_from_addr', $mail_system_return_path);

$ilSetting->set('mail_system_sys_signature', $signature);

$ilSetting->delete('prevent_smtp_globally');
$ilSetting->delete('mail_system_return_path');
$ilSetting->delete('mail_system_sender_name');
$ilSetting->delete('mail_external_sender_noreply');
?>