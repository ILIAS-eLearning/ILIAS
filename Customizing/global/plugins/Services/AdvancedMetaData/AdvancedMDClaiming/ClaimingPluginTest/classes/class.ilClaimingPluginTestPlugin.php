<?php

include_once("./Services/AdvancedMetaData/classes/class.ilAdvancedMDClaimingPlugin.php");
 
/**
* Advanced MD claiming test plugin
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*/
class ilClaimingPluginTestPlugin extends ilAdvancedMDClaimingPlugin
{
	public function getPluginName()
	{
		return "ClaimingPluginTest";
	}
	
	public function checkPermission($a_user_id, $a_context_type, $a_context_id, $a_action_id, $a_action_sub_id)
	{
		/* plugin testing
		if($a_context_type == ilAdvancedMDPermissionHelper::CONTEXT_SUBSTITUTION_CATEGORY
			&& $a_context_id == 12
			)
		{
			return false;
		}		
		*/
		
		return true;
	}
}

?>