<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/**
* Class ilObjectStatusGUI
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @package core
*/
class ilObjectStatusGUI
{
	var $lng;
	var $ilias;
	var $tpl;
	var $tree;
	var $rbacsystem;
	var $cur_ref_id;
	var $cmd;
	var $mode;
	var $ctrl;

	/**
	* Constructor
	* @access	public
	*/
	function ilObjectStatusGUI(&$a_obj)
	{
		global $ilUser,$ilCtrl,$ilias,$ilErr,$lng;

		$this->ctrl =& $ilCtrl;
		$this->object =& $a_obj;
		
		$this->tpl = new ilTemplate("tpl.info_layout.html", false, false);
		$this->tpl->setVariable("INFO_REMARK_INTERRUPTED",$lng->txt('info_remark_interrupted'));

		if (empty($_POST['Fuserid']))
		{
			$this->user =& $ilUser;
		}
		else
		{
			$this->user = $ilias->obj_factory->getInstanceByObjId($_POST['Fuserid'],false);

			if ($this->user === false)
			{
				$this->user =& $ilUser;
				$ilErr->raiseError($lng->txt("info_err_user_not_exist"),$ilErr->MESSAGE);
			}
		}

		$this->getPermissionInfo();
		
		$this->getRoleAssignmentInfo();
		
		$this->getObjectSummary();
		
		$this->displayUserChangeForm();
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$this->ctrl->setCmd("");
	}
	
	function &__initTableGUI()
	{
		include_once "class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}
	
	function __setTableGUIBasicData(&$tbl,&$result_set,$from = "")
	{
        global $lng;

		$tbl->disable('footer');
		$tbl->disable('linkbar');
		$tbl->disable('hits');
		$tbl->disable('sort');

		$tbl->setLimit(0);
		$tbl->setData($result_set);
	}
	
	function __showPermissionsTable($a_result_set)
	{
        global $lng;

        $tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// title & header columns
		$tbl->setTitle($lng->txt("info_access_permissions"),"icon_perm_b.gif",$lng->txt("info_access_permissions"));

		//user must be member
		$tbl->setHeaderNames(array("",$lng->txt("operation")));
		//$tbl->setHeaderVars(array("operation","granted"),$this->ctrl->getParameterArray($this->object,"",false));
		$tbl->setHeaderVars(array("","operation"),"");
		//$tbl->setColumnWidth(array("1%","20"));


		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->setStyle('table','std');
		$tbl->render();
		$this->tpl->setVariable('INFO_PERMISSIONS',$tbl->tpl->get());
		
		return $tbl->tpl->get();
	}
	
	function __showRolesTable($a_result_set)
	{
        global $lng;

        $tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// title & header columns
		$tbl->setTitle($lng->txt("info_available_roles"),"icon_rolf_b.gif",$lng->txt("info_available_roles"));

		$tbl->setHeaderNames(array("",$lng->txt("role"),str_replace(" ","&nbsp;",$lng->txt("info_permission_source")),str_replace(" ","&nbsp;",$lng->txt("info_permission_origin"))));

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->setStyle('table','std');
		$tbl->render();
		$this->tpl->setVariable('INFO_ROLES',$tbl->tpl->get());
		
		return $tbl->tpl->get();
	}
	
	function __showObjectSummaryTable($a_result_set)
	{
        global $lng;

        $tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		// title & header columns
		$tbl->setTitle($lng->txt("info_access_and_status_info"),"icon_".$this->object->getType()."_b.gif",$lng->txt("summary"));

		//user must be member
		$tbl->setHeaderNames(array("&nbsp;",""));
		//$tbl->setHeaderVars(array("operation","granted"),$this->ctrl->getParameterArray($this->object,"",false));
		$tbl->setHeaderVars(array("",""),"");
		$tbl->setColumnWidth(array("15%","85%"));


		$this->__setTableGUIBasicData($tbl,$a_result_set);
		//$tbl->setStyle('table','std');
		$tbl->render();
		$this->tpl->setVariable('INFO_SUMMARY',$tbl->tpl->get());
		
		return $tbl->tpl->get();
	}

	function getHTML()
	{
		return $this->tpl->get();
	}
	
	function getPermissionInfo()
	{
		global $ilAccess,$lng,$rbacreview,$ilUser;

		// icon handlers
		$icon_ok = "<img src=\"".ilUtil::getImagePath("icon_ok.gif")."\" alt=\"".$lng->txt("info_assigned")."\" title=\"".$lng->txt("info_assigned")."\" border=\"0\" vspace=\"0\"/>";
		$icon_not_ok = "<img src=\"".ilUtil::getImagePath("icon_not_ok.gif")."\" alt=\"".$lng->txt("info_not_assigned")."\" title=\"".$lng->txt("info_not_assigned")."\" border=\"0\" vspace=\"0\"/>";
		
		// get all possible operation of current object
		$ops_list = getOperationList($this->object->getType());
		
		$counter = 0;

		// check permissions of user
		foreach ($ops_list as $ops)
		{
			$access = $ilAccess->doRBACCheck($ops['operation'],"info",$this->object->getRefId(),$this->user->getId());

			$result_set[$counter][] = $access ? $icon_ok : $icon_not_ok;
			$result_set[$counter][] = $lng->txt($this->object->getType()."_".$ops['operation']);
	
			++$counter;
		}

		return $this->__showPermissionsTable($result_set);
	}
	
	function getRoleAssignmentInfo()
	{
		global $lng,$rbacreview,$tree;

		// icon handlers
		$icon_ok = "<img src=\"".ilUtil::getImagePath("icon_ok.gif")."\" alt=\"".$lng->txt("info_assigned")."\" title=\"".$lng->txt("info_assigned")."\" border=\"0\" vspace=\"0\"/>";
		$icon_not_ok = "<img src=\"".ilUtil::getImagePath("icon_not_ok.gif")."\" alt=\"".$lng->txt("info_not_assigned")."\" title=\"".$lng->txt("info_not_assigned")."\" border=\"0\" vspace=\"0\"/>";
		
		// get all user roles and all valid roles in scope
		$user_roles = $rbacreview->assignedRoles($this->user->getId());
		$global_roles = $rbacreview->getGlobalRoles();
		$valid_roles = $rbacreview->getParentRoleIds($this->object->getRefId());

		$path = array_reverse($tree->getPathId($this->object->getRefId()));
		
		//var_dump("<pre>",$valid_roles,$parent_roles,"</pre>");

		include_once ('class.ilObjRole.php');

		$counter = 0;

		foreach ($valid_roles as $role)
		{
			$result_set[$counter][] = in_array($role['obj_id'],$user_roles) ? $icon_ok : $icon_not_ok;
			$result_set[$counter][] = str_replace(" ","&nbsp;",ilObjRole::_getTranslation($role["title"]));
			
			if ($role['role_type'] != "linked")
			{
				$result_set[$counter][] = "";
			}
			else
			{
				$rolfs = $rbacreview->getFoldersAssignedToRole($role["obj_id"]);

				// ok, try to match the next rolf in path
				foreach ($path as $node)
				{
					if ($node == 1)
					{
						break;
					}
				
					$rolf = $rbacreview->getRoleFolderOfObject($node);

					if (in_array($rolf['ref_id'],$rolfs))
					{
						$nodedata = $tree->getNodeData($node);
						$result_set[$counter][] = $nodedata["title"];
						break;
					}							
				}
			}
			
			if (in_array($role['obj_id'],$global_roles))
			{
				$result_set[$counter][] = $lng->txt("global");
			}
			else
			{
				$rolf = $rbacreview->getFoldersAssignedToRole($role["obj_id"],true);
				$parent_node = $tree->getParentNodeData($rolf[0]);
				$result_set[$counter][] = $parent_node["title"];
			}
					
			++$counter;	
		}
		
		return $this->__showRolesTable($result_set);
	}
	
	function getObjectSummary()
	{
		global $lng,$rbacreview,$ilAccess,$ilias;
		$infos = array();

		$result_set[0][] = "<b>".$lng->txt("info_view_of_user")."</b>";
		$result_set[0][] = $this->user->getFullname()." (#".$this->user->getId().")";
		
		$result_set[1][] = "<b>".$lng->txt("object")."</b>";
		$result_set[1][] = $this->object->getTitle()." (#".$this->object->getId().") (ref#".$this->object->getRefId().")";

		$result_set[2][] = "<b>".$lng->txt("type")."</b>";
		$result_set[2][] = $lng->txt("obj_".$this->object->getType());
		
		$result_set[3][] = "<b>".$lng->txt("status")."</b>";

		$ilAccess->clear();
		$ilAccess->doTreeCheck("visible","info",$this->object->getRefId(),$this->user->getId());
		$infos = array_merge($infos,$ilAccess->getInfo());

		$ilAccess->clear();
		$ilAccess->doPathCheck("visible","info",$this->object->getRefId(),$this->user->getId(),true);
		$infos = array_merge($infos,$ilAccess->getInfo());

		$ilAccess->clear();
		$ilAccess->doConditionCheck("read","info",$this->object->getRefId(),$this->user->getId(),$this->object->getId(),$this->object->getType());
		$infos = array_merge($infos,$ilAccess->getInfo());
		
		$cmds = $this->getCommands($this->object->getType());

		$ilAccess->clear();		
		foreach ($cmds as $cmd)
		{

			$ilAccess->doStatusCheck($cmd['permission'],$cmd['cmd'],$this->object->getRefId(),$this->user->getId(),$this->object->getId(),$this->object->getType());
			$infos = array_merge($infos,$ilAccess->getInfo());
		}

		$alert = "il_ItemAlertProperty";
		$okay = "il_ItemOkayProperty";

		if (!$infos)
		{
			$text = "<span class=\"".$okay."\">".$lng->txt("access")."</span><br/> ";
		}
		else
		{
			foreach ($infos as $info)
			{
				switch ($info['type'])
				{
					case IL_STATUS_MESSAGE:
						$text .= "<span class=\"".$okay."\">".$info['text']."</span><br/> ";
						break;
						
					case IL_NO_PARENT_ACCESS:
						$obj = $ilias->obj_factory->getInstanceByRefId($info['data']);
						$text .= "<span class=\"".$alert."\">".$info['text']." (".$lng->txt("obj_".$obj->getType())." #".$obj->getId().": ".$obj->getTitle().")</span><br/> ";
						break;
						
						
					default:
						$text .= "<span class=\"".$alert."\">".$info['text']."</span><br/> ";
						break;
					
				}
			}
		}

		$result_set[3][] = $text;

		return $this->__showObjectSummaryTable($result_set);
	}
	
	function displayUserChangeForm()
	{
		global $lng;
		
		$class_name = get_class($this->object)."gui";

		$this->tpl->addBlockfile('INFO_USER_CHANGE_FORM','info_change_user_form','tpl.info_change_user.html');
		$this->tpl->setVariable("INFO_FORMACTION", $this->ctrl->getFormActionByClass($class_name,"info"));
		//$this->tpl->setVariable("INFO_FORMACTION", "");
		$this->tpl->setVariable('INFO_TXT_CHANGE_USER',$lng->txt('info_change_user_view'));
		$this->tpl->setVariable('INFO_CMD_CHANGE_USER','info');
		$this->tpl->setVariable('INFO_TXT_BTN_CHANGE_USER',$lng->txt('ok'));
		$this->tpl->setVariable('INFO_INPUT_USER_ID',$lng->txt('info_enter_user_id'));
		$this->tpl->setVariable("INFO_TXT_RESET_CURRENT_USER", $lng->txt('info_reset_current_user'));
		$this->tpl->setVariable("INFO_LINK_RESET_CURRENT_USER", "http://127.0.0.1/ilias3/repository.php?ref_id=27&cmdClass=ilobjcoursegui&cmd=info&cmdNode=6&baseClass=");
		
	}
	
	function getCommands($a_type)
	{
		global $objDefinition;
				
		$class = $objDefinition->getClassName($a_type);
		$location = $objDefinition->getLocation($a_type);
		$full_class = "ilObj".$class."Access";
		include_once($location."/class.".$full_class.".php");
		
		$cmds = call_user_func(array($full_class, "_getCommands"));
		
		array_push($cmds,array('permission' => 'visible','cmd' => 'info'));
		
		return $cmds;
	}
} // END class.ilObjectStatus

?>
