<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* This class displays the permission status of a user concerning a specific object.
* ("Permissions" -> "Permission of User")
*
* @author Sascha Hofmann <saschahofmann@gmx.de>
* @version $Id$
*
* @ingroup	ServicesAccessControl
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
		global $ilUser,$ilCtrl,$ilias,$ilErr,$lng,$rbacreview;

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
			if ($_POST['Fselect_type'] == "id")
			{
				$this->user = $ilias->obj_factory->getInstanceByObjId($_POST['Fuserid'],false);
			}
			else
			{
				include_once('Services/User/classes/class.ilObjUser.php');
				$user_id = ilObjUser::_lookupId($_POST['Fuserid']);
				$this->user = $ilias->obj_factory->getInstanceByObjId($user_id,false);
			}

			if ($this->user === false or $this->user->getType() != 'usr')
			{
				$this->user =& $ilUser;
				ilUtil::sendFailure($lng->txt('info_err_user_not_exist'));
			}
			else
			{
				ilUtil::sendInfo($lng->txt('info_user_view_changed'));
			}
		}
		
		// get all user roles and all valid roles in scope
		$this->user_roles = $rbacreview->assignedRoles($this->user->getId());
		$this->global_roles = $rbacreview->getGlobalRoles();
		$this->valid_roles = $rbacreview->getParentRoleIds($this->object->getRefId());
		$this->assigned_valid_roles = $this->getAssignedValidRoles();

		$this->getPermissionInfo();
		
		$this->getRoleAssignmentInfo();
		
		$this->getObjectSummary();

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
		include_once "./Services/Table/classes/class.ilTableGUI.php";

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
		$tbl->setTitle($lng->txt("info_access_permissions"),"icon_perm.gif",$lng->txt("info_access_permissions"));

		//user must be member
		$tbl->setHeaderNames(array("",$lng->txt("operation"),$lng->txt("info_from_role")));
		//$tbl->setHeaderVars(array("operation","granted"),$this->ctrl->getParameterArray($this->object,"",false));
		$tbl->setHeaderVars(array("","operation","role"),"");
		$tbl->setColumnWidth(array("1%","39%","60%"));


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
		$tbl->setTitle($lng->txt("info_available_roles"),"icon_rolf.gif",$lng->txt("info_available_roles"));

		$tbl->setHeaderNames(array("",$lng->txt("role"),str_replace(" ","&nbsp;",$lng->txt("info_permission_source")),str_replace(" ","&nbsp;",$lng->txt("info_permission_origin"))));
		$tbl->setColumnWidth(array("1%","19%","40%","40%"));
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
		
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormActionByClass("ilpermissiongui","info"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		
        $tpl->setCurrentBlock("plain_button");
		$tpl->setVariable("PBTN_NAME","info");
		$tpl->setVariable("PBTN_VALUE",$lng->txt("info_change_user_view"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("plain_buttons");
		$tpl->parseCurrentBlock();
		
		$tpl->setVariable("COLUMN_COUNTS",7);
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("spacer.gif"));

		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);

		// title & header columns
		$tbl->setTitle($lng->txt("info_access_and_status_info"));

		//user must be member
		$tbl->setHeaderNames(array("&nbsp;",$lng->txt("info_enter_login_or_id")));
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
	
	function getAssignedValidRoles()
	{
		global $rbacreview;
		
		include_once ('./Services/AccessControl/classes/class.ilObjRole.php');
		$assigned_valid_roles = array();

		foreach ($this->valid_roles as $role)
		{
			if (in_array($role['obj_id'],$this->user_roles))
			{
				if ($role["obj_id"] == SYSTEM_ROLE_ID)
				{
					// get all possible operation of current object
					$ops_list = ilRbacReview::_getOperationList($this->object->getType());
					
					foreach ($ops_list as $ops_data)
					{
						$ops[] = (int) $ops_data['ops_id'];
					}
					
					$role['ops'] = $ops;
				}
				else
				{
					$role['ops'] = $rbacreview->getRoleOperationsOnObject($role["obj_id"],$this->object->getRefId());
				}
				
				include_once('./Services/AccessControl/classes/class.ilObjRole.php');
				$role['translation'] = str_replace(" ","&nbsp;",ilObjRole::_getTranslation($role["title"]));
				$assigned_valid_roles[] = $role;
			}
		}
		
		return $assigned_valid_roles;
	}
	
	function getPermissionInfo()
	{
		global $ilAccess,$lng,$rbacreview,$ilUser,$ilObjDataCache;

		// icon handlers
		$icon_ok = "<img src=\"".ilUtil::getImagePath("icon_ok.gif")."\" alt=\"".$lng->txt("info_assigned")."\" title=\"".$lng->txt("info_assigned")."\" border=\"0\" vspace=\"0\"/>";
		$icon_not_ok = "<img src=\"".ilUtil::getImagePath("icon_not_ok.gif")."\" alt=\"".$lng->txt("info_not_assigned")."\" title=\"".$lng->txt("info_not_assigned")."\" border=\"0\" vspace=\"0\"/>";
		
		// get all possible operation of current object
		$ops_list = ilRbacReview::_getOperationList($this->object->getType());
		
		$counter = 0;

		// check permissions of user
		foreach ($ops_list as $ops)
		{
			$access = $ilAccess->doRBACCheck($ops['operation'],"info",$this->object->getRefId(),$this->user->getId());

			$result_set[$counter][] = $access ? $icon_ok : $icon_not_ok;
			$result_set[$counter][] = $lng->txt($this->object->getType()."_".$ops['operation']);
			
			$list_role = "";

			// Check ownership
			if($this->user->getId() == $ilObjDataCache->lookupOwner($this->object->getId()))
			{
				$list_role[] = $lng->txt('info_owner_of_object');
			}
			// get operations on object for each assigned role to user
			foreach ($this->assigned_valid_roles as $role)
			{
				if (in_array($ops['ops_id'],$role['ops']))
				{
					$list_role[] = $role['translation'];
				}
			}
			
			if (empty($list_role))
			{
				$roles_formatted = $lng->txt('none');
			}
			else
			{
				$roles_formatted = implode("<br/>",$list_role);
			}

			$result_set[$counter][] = $roles_formatted;
	
			++$counter;
		}

		return $this->__showPermissionsTable($result_set);
	}
	
	function getRoleAssignmentInfo()
	{
		global $lng,$rbacreview,$tree;

		include_once('./Services/AccessControl/classes/class.ilObjRole.php');

		// icon handlers
		$icon_ok = "<img src=\"".ilUtil::getImagePath("icon_ok.gif")."\" alt=\"".$lng->txt("info_assigned")."\" title=\"".$lng->txt("info_assigned")."\" border=\"0\" vspace=\"0\"/>";
		$icon_not_ok = "<img src=\"".ilUtil::getImagePath("icon_not_ok.gif")."\" alt=\"".$lng->txt("info_not_assigned")."\" title=\"".$lng->txt("info_not_assigned")."\" border=\"0\" vspace=\"0\"/>";

		$path = array_reverse($tree->getPathId($this->object->getRefId()));
		
		include_once ('./Services/AccessControl/classes/class.ilObjRole.php');
		$counter = 0;
		foreach ($this->valid_roles as $role)
		{
			$result_set[$counter][] = in_array($role['obj_id'],$this->user_roles) ? $icon_ok : $icon_not_ok;
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
			
			if (in_array($role['obj_id'],$this->global_roles))
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
		
		$input_field = "<input class=\"std\" type=\"input\" name=\"Fuserid\" value=\"".$this->user->getLogin()."\"/>";
		$input_radio_login = "<input class=\"std\" id=\"select_type_login\" type=\"radio\" name=\"Fselect_type\" value=\"login\" checked=\"checked\" />";
		$input_radio_id = "<input class=\"std\" id=\"select_type_id\" type=\"radio\" name=\"Fselect_type\" value=\"id\" />";

		$result_set[0][] = "&nbsp;";
		$result_set[0][] = $input_field."&nbsp;".$input_radio_login."<label for=\"select_type_login\">".$lng->txt('login')."</label>".$input_radio_id."<label for=\"select_type_id\">".$lng->txt('id')."</label>";
		
		$result_set[1][] = "<b>".$lng->txt("info_view_of_user")."</b>";
		$result_set[1][] = $this->user->getFullname()." (#".$this->user->getId().")";

		$assigned_valid_roles = array();

		foreach ($this->assigned_valid_roles as $role)
		{
			$assigned_valid_roles[] = $role["translation"];
		}
		
		$roles_str = implode(", ",$assigned_valid_roles);
			
		$result_set[2][] = "<b>".$lng->txt("roles")."</b>";
		$result_set[2][] = $roles_str;
		
		$result_set[4][] = "<b>".$lng->txt("status")."</b>";

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

		foreach ($cmds as $cmd)
		{
			$ilAccess->clear();
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

		$result_set[4][] = $text;

		return $this->__showObjectSummaryTable($result_set);
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
