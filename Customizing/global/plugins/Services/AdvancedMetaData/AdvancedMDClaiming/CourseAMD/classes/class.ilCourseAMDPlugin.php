<?php

require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDClaimingPlugin.php");
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDFieldDefinition.php");
require_once("Services/AdvancedMetaData/classes/class.ilAdvancedMDPermissionHelper.php");
 
/**
* Advanced MD claiming test plugin
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*/
class ilCourseAMDPlugin extends ilAdvancedMDClaimingPlugin
{
	public function getPluginName()
	{
		return "CourseAMD";
	}

	public function checkPermission($a_user_id, $a_context_type, $a_context_id, $a_action_id, $a_action_sub_id)
	{
		return false;
	}
}

?>