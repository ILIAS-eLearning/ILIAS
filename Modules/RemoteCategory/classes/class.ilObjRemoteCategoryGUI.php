<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once('Services/WebServices/ECS/classes/class.ilRemoteObjectBaseGUI.php');

/** 
* Remote category GUI 
* 
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
* 
* 
* @ilCtrl_Calls ilObjRemoteCategoryGUI: ilPermissionGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjRemoteCategoryGUI: ilCommonActionDispatcherGUI
* @ingroup ModulesRemoteCategory
*/

class ilObjRemoteCategoryGUI extends ilRemoteObjectBaseGUI
{
	public function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		parent::__construct($a_id, $a_id_type, $a_parent_node_id);

		$this->lng->loadLanguageModule('rcat');
		$this->lng->loadLanguageModule('cat');
	}
	
	function getType()
	{
		return 'rcat';
	}
}

?>