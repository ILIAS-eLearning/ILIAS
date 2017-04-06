<#1>
<?php
// Delete before merge!!!!!!!!!!!

$client_id       = basename(CLIENT_DATA_DIR);
$client_ini_file = ILIAS_ABSOLUTE_PATH . '/' . ILIAS_WEB_DIR . '/' . $client_id . '/client.ini.php';

$ilClientIniFile = new ilIniFile($client_ini_file);

if(!$ilClientIniFile->read())
{
	echo sprintf("Could not read client ini file: %s", $client_ini_file);
	exit();
}

$signature = "\n\n* * * * *\n";

$signature     .= $ilClientIniFile->readVariable('client', 'name') . "\n";
if(strlen($desc = $ilClientIniFile->readVariable('client', 'description')))
{
	$signature .= $desc . "\n";
}
$signature .= ILIAS_HTTP_PATH;
$clientdirs = glob(ILIAS_WEB_DIR . '/*', GLOB_ONLYDIR);

if(is_array($clientdirs) && count($clientdirs) > 1)
{
	$signature .= '/login.php?client_id=' . CLIENT_ID;
}
$signature .= "\n\n";

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