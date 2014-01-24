<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";


/**
* Organisation Unit Tree
*
* @author	Bjoern Heyser <bheyser@databay.de>
* @version	$Id$
*
* @ingroup ServicesOrgUnit
* 
* @ilctrl_isCalledBy ilOrgUnitTreeGUI: ilObjUserFolderGUI, ilObjUserGUI
*/
class ilOrgUnitTreeGUI //extends ilObjectGUI
{
	public function executeCommand()
	{
		global $ilCtrl;
		
		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd();
		#$this->prepareOutput();

		switch($next_class)
		{
			/*case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;*/

			default:
				if(!$cmd)
				{
					$cmd = "viewTree";
				}
				$cmd .= "Object";

				$this->$cmd();

				break;
		}
		return true;
	}

	public function viewTreeObject()
	{
		global $tpl, $lng;

		require_once('Services/OrgUnit/classes/class.ilOrgUnitTree.php');
		$tree = new ilOrgUnitTree();

		$root_unit = $tree->getRecursiveOrgUnitTree();

		$debug = '<pre>'.print_r($nodes,1).'</pre>';
		
		require_once('Services/OrgUnit/classes/class.ilOrgUnitTreeExplorerGUI.php');
		$exp = new ilOrgUnitTreeExplorerGUI($root_unit);

		$tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.org_unit_tree.html', 'Services/OrgUnit');

		$tpl->setCurrentBlock('adm_content');

		$tpl->setVariable('ORG_UNIT_TREE_HEADER', $lng->txt('org_unit_tree_header'));

		$tpl->setVariable('ORG_UNIT_TREE_EXPLORER', $exp->getHTML());

		$tpl->parseCurrentBlock();
	}


	public function viewAssignedUnitsObject()
	{
		global $tpl, $lng;

		$user_id = (int)$_GET['obj_id'];

		require_once('Services/OrgUnit/classes/class.ilOrgUnitAssignmentTableGUI.php');
		$tbl = new ilOrgUnitAssignmentTableGUI($this, 'viewAssignedUnits',$user_id);
		
		$tpl->setVariable('ADM_CONTENT', $tbl->getHTML());
	}

}

?>
