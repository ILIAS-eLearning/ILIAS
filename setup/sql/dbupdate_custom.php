<#1>
<?php

include_once './Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php';
ilDBUpdateNewObjectType::addRBACTemplate(
	'sess', 
	'il_sess_participant', 
	'Session participant template', 
	[
		ilDBUpdateNewObjectType::getCustomRBACOperationId('visible'),
		ilDBUpdateNewObjectType::getCustomRBACOperationId('read')
	]
);
?>