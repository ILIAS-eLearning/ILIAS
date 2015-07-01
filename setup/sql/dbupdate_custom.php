<#1>
<?php

include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');				
$blog_type_id = ilDBUpdateNewObjectType::getObjectTypeId('blog');
if($blog_type_id)
{					
	// not sure if we want to clone "write" or "contribute"?
	$new_ops_id = ilDBUpdateNewObjectType::addCustomRBACOperation('redact', 'Redact', 'object', 3204);	
	if($new_ops_id)
	{
		ilDBUpdateNewObjectType::addRBACOperation($blog_type_id, $new_ops_id);						
	}
}	

?>
<#2>
<?php

$redact_ops_id = ilDBUpdateNewObjectType::getCustomRBACOperationId('redact');
if($redact_ops_id)
{
	include_once('./Services/Migration/DBUpdate_3560/classes/class.ilDBUpdateNewObjectType.php');
	ilDBUpdateNewObjectType::addRBACTemplate('blog', 'il_blog_editor', 'Editor template for blogs', 
		array(
			ilDBUpdateNewObjectType::RBAC_OP_VISIBLE,
			ilDBUpdateNewObjectType::RBAC_OP_READ,
			ilDBUpdateNewObjectType::RBAC_OP_WRITE,
			$redact_ops_id)
	);
}

?>