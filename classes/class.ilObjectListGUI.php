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


define ("IL_LIST_AS_TRIGGER", "trigger");
define ("IL_LIST_FULL", "full");


/**
* Class ilObjectListGUI
*
* Important note:
*
* All access checking should be made within $ilAccess and
* the checkAccess of the ilObj...Access classes. Do not additionally
* enable or disable any commands within this GUI class or in derived
* classes, except when the container (e.g. a search result list)
* generally refuses them.
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
*/
class ilObjectListGUI
{
	var $ctrl;
	var $description_enabled = true;
	var $preconditions_enabled = true;
	var $properties_enabled = true;
	var $notice_properties_enabled = true;
	var $commands_enabled = true;
	var $cust_prop = array();
	var $cust_commands = array();
	var $info_screen_enabled = false;
	var $condition_depth = 0;
	var $std_cmd_only = false;

	protected $substitutions = null;
	protected $substitutions_enabled = false;
	/**
	* constructor
	*
	*/
	function ilObjectListGUI()
	{
		global $rbacsystem, $ilCtrl, $lng, $ilias;

		$this->rbacsystem = $rbacsystem;
		$this->ilias = $ilias;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->mode = IL_LIST_FULL;
		$this->path_enabled = false;
		
//echo "list";
		$this->init();
		
		include_once('Services/LDAP/classes/class.ilLDAPRoleGroupMapping.php');
		$this->ldap_mapping = ilLDAPRoleGroupMapping::_getInstance();
	}


	/**
	* set the container object (e.g categorygui)
	* Used for link, delete ... commands
	*
	* this method should be overwritten by derived classes
	*/
	function setContainerObject(&$container_obj)
	{
		$this->container_obj =& $container_obj;
	}


	/**
	* initialisation
	*
	* this method should be overwritten by derived classes
	*/
	function init()
	{
		// Create static links for default command (linked title) or not
		$this->static_link_enabled = true;
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = false;
		$this->payment_enabled = false;
		$this->notice_properties_enabled = true;
		$this->info_screen_enabled = false;
		$this->type = "";					// "cat", "course", ...
		$this->gui_class_name = "";			// "ilobjcategorygui", "ilobjcoursegui", ...

		// general commands array, e.g.
		include_once('class.ilObjectAccess.php');
		$this->commands = ilObjectAccess::_getCommands();
	}

	// Single get set methods
	/**
	* En/disable properties
	*
	* @param bool
	* @return void
	*/
	function enableProperties($a_status)
	{
		$this->properties_enabled = $a_status;

		return;
	}
	/**
	*
	*
	* @param bool
	* @return bool
	*/
	function getPropertiesStatus()
	{
		return $this->properties_enabled;
	}
	/**
	* En/disable preconditions
	*
	* @param bool
	* @return void
	*/
	function enablePreconditions($a_status)
	{
		$this->preconditions_enabled = $a_status;

		return;
	}
	
	function getNoticePropertiesStatus()
	{
		return $this->notice_properties_enabled;
	}
	
	/**
	* En/disable notices
	*
	* @param bool
	* @return void
	*/
	function enableNoticeProperties($a_status)
	{
		$this->notice_properties_enabled = $a_status;

		return;
	}
	/**
	*
	*
	* @param bool
	* @return bool
	*/
	function getPreconditionsStatus()
	{
		return $this->preconditions_enabled;
	}
	/**
	* En/disable description
	*
	* @param bool
	* @return void
	*/
	function enableDescription($a_status)
	{
		$this->description_enabled = $a_status;

		return;
	}
	/**
	*
	*
	* @param bool
	* @return bool
	*/
	function getDescriptionStatus()
	{
		return $this->description_enabled;
	}
	/**
	* En/disable delete
	*
	* @param bool
	* @return void
	*/
	function enableDelete($a_status)
	{
		$this->delete_enabled = $a_status;

		return;
	}
	/**
	*
	*
	* @param bool
	* @return bool
	*/
	function getDeleteStatus()
	{
		return $this->delete_enabled;
	}
	/**
	* En/disable cut
	*
	* @param bool
	* @return void
	*/
	function enableCut($a_status)
	{
		$this->cut_enabled = $a_status;

		return;
	}
	/**
	*
	* @param bool
	* @return bool
	*/
	function getCutStatus()
	{
		return $this->cut_enabled;
	}
	/**
	* En/disable subscribe
	*
	* @param bool
	* @return void
	*/
	function enableSubscribe($a_status)
	{
		$this->subscribe_enabled = $a_status;

		return;
	}
	/**
	*
	* @param bool
	* @return bool
	*/
	function getSubscribeStatus()
	{
		return $this->subscribe_enabled;
	}
	/**
	* En/disable payment
	*
	* @param bool
	* @return void
	*/
	function enablePayment($a_status)
	{
		$this->payment_enabled = $a_status;

		return;
	}
	/**
	*
	* @param bool
	* @return bool
	*/
	function getPaymentStatus()
	{
		return $this->payment_enabled;
	}
	/**
	* En/disable link
	*
	* @param bool
	* @return void
	*/
	function enableLink($a_status)
	{
		$this->link_enabled = $a_status;

		return;
	}
	/**
	*
	* @param bool
	* @return bool
	*/
	function getLinkStatus()
	{
		return $this->link_enabled;
	}

	/**
	* En/disable path
	*
	* @param bool
	* @return void
	*/
	function enablePath($a_path)
	{
		$this->path_enabled = $a_path;
	}

	/**
	*
	* @param bool
	* @return bool
	*/
	function getPathStatus()
	{
		return $this->path_enabled;
	}
	
	/**
	* En/disable commands
	*
	* @param bool
	* @return void
	*/
	function enableCommands($a_status, $a_std_only = false)
	{
		$this->commands_enabled = $a_status;
		$this->std_cmd_only = $a_std_only;
	}
	/**
	*
	* @param bool
	* @return bool
	*/
	function getCommandsStatus()
	{
		return $this->commands_enabled;
	}

	/**
	* En/disable path
	*
	* @param bool
	* @return void
	*/
	function enableInfoScreen($a_info_screen)
	{
		$this->info_screen_enabled = $a_info_screen;
	}

	/**
	*
	* @param bool
	* @return bool
	*/
	function getInfoScreenStatus()
	{
		return $this->info_screen_enabled;
	}
	
	/**
	 * Enable substitutions
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function enableSubstitutions($a_status)
	{
	 	$this->substitutions_enabled = $a_status;
	}
	
	/**
	 * Get substitution status
	 *
	 * @access public
	 * 
	 */
	public function getSubstitutionStatus()
	{
	 	return $this->substitutions_enabled;
	}

	/**
	* @param string title
	* @return bool
	*/
	function setTitle($a_title)
	{
		$this->title = $a_title;
	}

	/**
	 * getTitle overwritten in class.ilObjLinkResourceList.php 
	 *
	 * @return string title
	 */
	function getTitle()
	{
		return $this->title;
	}

	/**
	* @param string description
	* @return bool
	*/
	function setDescription($a_description)
	{
		$this->description = $a_description;
	}

	/**
	 * getDescription overwritten in class.ilObjLinkResourceList.php 
	 *
	 * @return string description
	 */
	function getDescription()
	{
		return $this->description;
	}

	/**
	* inititialize new item (is called by getItemHTML())
	*
	* @param	int			$a_ref_id		reference id
	* @param	int			$a_obj_id		object id
	* @param	string		$a_title		title
	* @param	string		$a_description	description
	*/
	function initItem($a_ref_id, $a_obj_id, $a_title = "", $a_description = "")
	{
		$this->ref_id = $a_ref_id;
		$this->obj_id = $a_obj_id;
		$this->setTitle($a_title);
		$this->setDescription($a_description);
		#$this->description = $a_description;
				
		// checks, whether any admin commands are included in the output
		$this->adm_commands_included = false;
	}
	
	/**
	 * Get default command link
	 * Overwritten for e.g categories,courses => they return a goto link
	 * If search engine visibility is enabled these object type return a goto_CLIENT_ID_cat_99.html link
	 *
	 * @access public
	 * @param int command link
	 * 
	 */
	public function createDefaultCommand($command)
	{
		if($this->static_link_enabled)
		{
		 	include_once('classes/class.ilLink.php');
		 	if($link = ilLink::_getStaticLink($this->ref_id,$this->type,false))
		 	{
		 		$command['link'] = $link;
		 		$command['frame'] = '_top';
		 	}
		}
	 	return $command;
	}


	/**
	* Get command link url.
	*
	* Overwrite this method, if link target is not build by ctrl class
	* (e.g. "forum.php"). This is the case
	* for all links now, but bringing everything to ilCtrl should
	* be realised in the future.
	*
	* @param	string		$a_cmd			command
	*
	* @return	string		command link url
	*/
	function getCommandLink($a_cmd)
	{
		// don't use ctrl here in the moment
		return 'repository.php?ref_id='.$this->ref_id.'&cmd='.$a_cmd;

		// separate method for this line
		$cmd_link = $this->ctrl->getLinkTargetByClass($this->gui_class_name,
			$a_cmd);
		return $cmd_link;
	}


	/**
	* Get command target frame.
	*
	* Overwrite this method if link frame is not current frame
	*
	* @param	string		$a_cmd			command
	*
	* @return	string		command target frame
	*/
	function getCommandFrame($a_cmd)
	{
		return "";
	}


	/**
	* Get item properties
	*
	* Overwrite this method to add properties at
	* the bottom of the item html
	*
	* @return	array		array of property arrays:
	*						"alert" (boolean) => display as an alert property (usually in red)
	*						"property" (string) => property name
	*						"value" (string) => property value
	*/
	function getProperties($a_item = '')
	{
		$props = array();
		// please list alert properties first
		// example (use $lng->txt instead of "Status"/"Offline" strings):
		// $props[] = array("alert" => true, "property" => "Status", "value" => "Offline");
		// $props[] = array("alert" => false, "property" => ..., "value" => ...);
		// ...

		return $props;
	}
	
	/**
	* add custom property
	*/
	function addCustomProperty($a_property = "", $a_value = "",
		$a_alert = false, $a_newline = false)
	{
		$this->cust_prop[] = array("property" => $a_property, "value" => $a_value,
			"alert" => $a_alert, "newline" => $a_newline);
	}
	
	/**
	* get custom properties
	*/
	function getCustomProperties($a_prop)
	{
		if (is_array($this->cust_prop))
		{
			foreach($this->cust_prop as $prop)
			{
				$a_prop[] = $prop;
			}
		}
		return $a_prop;
	}
	
	/**
	* get notice properties
	*/
	function getNoticeProperties()
	{
		$this->notice_prop = array();
		if($infos = $this->ldap_mapping->getInfoStrings($this->obj_id,true))
		{
			foreach($infos as $info)
			{
				$this->notice_prop[] = array('value' => $info);
			}
		}		
		return $this->notice_prop ? $this->notice_prop : array();
	}	
	/**
	* add a custom command
	*/
	function addCustomCommand($a_link, $a_lang_var, $a_frame = "")
	{
		$this->cust_commands[] =
			array("link" => $a_link, "lang_var" => $a_lang_var,
			"frame" => $a_frame);
	}


	/**
	* get all current commands for a specific ref id (in the permission
	* context of the current user)
	*
	* !!!NOTE!!!: Please use getListHTML() if you want to display the item
	* including all commands
	*
	* !!!NOTE 2!!!: Please do not overwrite this method in derived
	* classes becaus it will get pretty large and much code will be simply
	* copy-and-pasted. Insert smaller object type related method calls instead.
	* (like getCommandLink() or getCommandFrame())
	*
	* @access	public
	* @param	int		$a_ref_id		ref id of object
	* @return	array	array of command arrays including
	*					"permission" => permission name
	*					"cmd" => command
	*					"link" => command link url
	*					"frame" => command link frame
	*					"lang_var" => language variable of command
	*					"granted" => true/false: command granted or not
	*					"access_info" => access info object (to do: implementation)
	*/
	function getCommands()
	{
		global $ilAccess, $ilBench;

		$ref_commands = array();
		foreach($this->commands as $command)
		{
			$permission = $command["permission"];
			$cmd = $command["cmd"];
			$lang_var = $command["lang_var"];

			// all access checking should be made within $ilAccess and
			// the checkAccess of the ilObj...Access classes
			$ilBench->start("ilObjectListGUI", "4110_get_commands_check_access");
			$access = $ilAccess->checkAccess($permission, $cmd, $this->ref_id, $this->type);
			$ilBench->stop("ilObjectListGUI", "4110_get_commands_check_access");

			if ($access)
			{
				$cmd_link = $this->getCommandLink($command["cmd"]);
				$cmd_frame = $this->getCommandFrame($command["cmd"]);
				$access_granted = true;
			}
			else
			{
				$access_granted = false;
				$info_object = $ilAccess->getInfo();
			}

			$ref_commands[] = array(
				"permission" => $permission,
				"cmd" => $cmd,
				"link" => $cmd_link,
				"frame" => $cmd_frame,
				"lang_var" => $lang_var,
				"granted" => $access_granted,
				"access_info" => $info_object,
				"default" => $command["default"]
			);
		}

		return $ref_commands;
	}


	/**
	* insert item title
	*
	* @access	private
	* @param	object		$a_tpl		template object
	* @param	string		$a_title	item title
	*/
	function insertTitle()
	{
		if (!$this->default_command || !$this->getCommandsStatus())
		{
			$this->tpl->setCurrentBlock("item_title");
			$this->tpl->setVariable("TXT_TITLE", $this->getTitle());
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			if ($this->default_command["frame"] != "")
			{
				$this->tpl->setCurrentBlock("title_linked_frame");
				$this->tpl->setVariable("TARGET_TITLE_LINKED", $this->default_command["frame"]);
				$this->tpl->parseCurrentBlock();
			}
			
			// workaround for repository frameset
			#var_dump("<pre>",$this->default_command['link'],"</pre>");
			$this->default_command["link"] = 
				$this->appendRepositoryFrameParameter($this->default_command["link"]);
				
			#var_dump("<pre>",$this->default_command['link'],"</pre>");
			

			// the default command is linked with the title
			$this->tpl->setCurrentBlock("item_title_linked");
			$this->tpl->setVariable("TXT_TITLE_LINKED", $this->getTitle());
			$this->tpl->setVariable("HREF_TITLE_LINKED", $this->default_command["link"]);
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	 * Insert substitutions 
	 *
	 * @access public
	 * 
	 */
	public function insertSubstitutions()
	{
		$this->tpl->setCurrentBlock('item_adv_substitutions');
		$this->tpl->setVariable('ITEM_SUBSTITUTIONS',$this->substitutions->substitute($this->ref_id,$this->obj_id));
		$this->tpl->parseCurrentBlock();
	}


	/**
	* insert item description
	*
	* @access	private
	* @param	object		$a_tpl		template object
	* @param	string		$a_desc		item description
	*/
	function insertDescription()
	{
		if($this->getSubstitutionStatus())
		{
			$this->insertSubstitutions();
			if(!$this->substitutions->isDescriptionEnabled())
			{
				return true;
			}
		}
		
		$this->tpl->setCurrentBlock("item_description");
		$this->tpl->setVariable("TXT_DESC", $this->getDescription());
		$this->tpl->parseCurrentBlock();
	}

	/**
	* set output mode
	*
	* @param	string	$a_mode		output mode (IL_LIST_FULL | IL_LIST_AS_TRIGGER)
	*/
	function setMode($a_mode)
	{
		$this->mode = $a_mode;
	}

	/**
	* get output mode
	*
	* @return	string		output mode (IL_LIST_FULL | IL_LIST_AS_TRIGGER)
	*/
	function getMode()
	{
		return $this->mode;
	}
	
	/**
	* set depth for precondition output (stops at level 5)
	*/
	function setConditionDepth($a_depth)
	{
		$this->condition_depth = $a_depth;
	}

	/**
	* check current output mode
	*
	* @param	string		$a_mode (IL_LIST_FULL | IL_LIST_AS_TRIGGER)
	*
	* @return 	boolen		true if current mode is $a_mode
	*/
	function isMode($a_mode)
	{
		if ($a_mode == $this->mode)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	* insert properties
	*
	* @access	private
	*/
	function insertProperties($a_item = '')
	{
		global $ilAccess, $lng;

		$props = $this->getProperties($a_item);
		$props = $this->getCustomProperties($props);

		// add no item access note in public section
		// for items that are visible but not readable
		if ($this->ilias->account->getId() == ANONYMOUS_USER_ID)
		{
			if (!$ilAccess->checkAccess("read", "", $this->ref_id, $this->type, $this->obj_id))
			{
				$props[] = array("alert" => true,
					"value" => $lng->txt("no_access_item_public"),
					"newline" => true);
			}
		}

		$cnt = 1;
		if (is_array($props) && count($props) > 0)
		{
			foreach($props as $prop)
			{
				if ($prop["alert"] == true)
				{
					$this->tpl->touchBlock("alert_prop");
				}
				else
				{
					$this->tpl->touchBlock("std_prop");
				}
				if ($prop["newline"] == true && $cnt > 1)
				{
					$this->tpl->touchBlock("newline_prop");
				}
				if (isset($prop["property"]))
				{
					$this->tpl->setCurrentBlock("prop_name");
					$this->tpl->setVariable("TXT_PROP", $prop["property"]);
					$this->tpl->parseCurrentBlock();
				}
				$this->tpl->setCurrentBlock("item_property");
				$this->tpl->setVariable("VAL_PROP", $prop["value"]);
				$this->tpl->parseCurrentBlock();

				$cnt++;
			}
			$this->tpl->setCurrentBlock("item_properties");
			$this->tpl->parseCurrentBlock();
		}
	}
	
	function insertNoticeProperties()
	{
		$this->getNoticeProperties();
		foreach($this->notice_prop as $property)
		{
			$this->tpl->setCurrentBlock('notice_item');
			$this->tpl->setVariable('NOTICE_ITEM_VALUE',$property['value']);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setCurrentBlock('notice_property');
		$this->tpl->parseCurrentBlock();
	}


	/**
	* insert payment information
	*
	* @access	private
	*/
	function insertPayment()
	{
		include_once './payment/classes/class.ilPaymentObject.php';

		if ($this->payment_enabled &&
			ilPaymentObject::_isBuyable($this->ref_id))
		{
			if (ilPaymentObject::_hasAccess($this->ref_id))
			{
				$this->tpl->setCurrentBlock("payment");
				$this->tpl->setVariable("PAYMENT_TYPE_IMG", ilUtil::getImagePath('icon_pays_access_b.gif'));
				$this->tpl->setVariable("PAYMENT_ALT_IMG", $this->lng->txt('payment_system') . ": " . $this->lng->txt('payment_payed_access'));
				$this->tpl->parseCurrentBlock();
			}
			else if (ilPaymentObject::_isInCart($this->ref_id))
			{
				$this->tpl->setCurrentBlock("payment");
				$this->tpl->setVariable("PAYMENT_TYPE_IMG", ilUtil::getImagePath('icon_pays_cart_b.gif'));
				$this->tpl->setVariable("PAYMENT_ALT_IMG", $this->lng->txt('payment_system') . ": " . $this->lng->txt('payment_in_sc'));
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("payment");
				$this->tpl->setVariable("PAYMENT_TYPE_IMG", ilUtil::getImagePath('icon_pays_b.gif'));
				$this->tpl->setVariable("PAYMENT_ALT_IMG", $this->lng->txt('payment_system') . ": " . $this->lng->txt('payment_buyable'));
				$this->tpl->parseCurrentBlock();
			}
		}
	}

	/**
	* insert all missing preconditions
	*/
	function insertPreconditions()
	{
		global $ilAccess, $lng, $objDefinition;

		include_once("classes/class.ilConditionHandler.php");

		$missing_cond_exist = false;
		
		// do not show multi level conditions (messes up layout)
		if ($this->condition_depth > 0)
		{
			return;
		}

		foreach(ilConditionHandler::_getConditionsOfTarget($this->obj_id) as $condition)
		{
			if(ilConditionHandler::_checkCondition($condition['id']))
			{
				continue;
			}
			$missing_cond_exist = true;

			$cond_txt = $lng->txt("condition_".$condition["operator"])." ".
				$condition["value"];

			// display trigger item
			$class = $objDefinition->getClassName($condition["trigger_type"]);
			$location = $objDefinition->getLocation($condition["trigger_type"]);
			$full_class = "ilObj".$class."ListGUI";
			include_once($location."/class.".$full_class.".php");
			$item_list_gui = new $full_class($this);
			$item_list_gui->setMode(IL_LIST_AS_TRIGGER);
			$item_list_gui->enablePath(true);
			$item_list_gui->setConditionDepth($this->condition_depth + 1);
			$trigger_html = $item_list_gui->getListItemHTML($condition['trigger_ref_id'],
				$condition['trigger_obj_id'], trim($cond_txt).": ".ilObject::_lookupTitle($condition["trigger_obj_id"]),
				 "");
			$this->tpl->setCurrentBlock("precondition");
			if ($trigger_html == "")
			{
				$trigger_html = $this->lng->txt("precondition_not_accessible");
			}
			//$this->tpl->setVariable("TXT_CONDITION", trim($cond_txt));
			$this->tpl->setVariable("TRIGGER_ITEM", $trigger_html);
			$this->tpl->parseCurrentBlock();
		}

		if ($missing_cond_exist)
		{
			$this->tpl->setCurrentBlock("preconditions");
			$this->tpl->setVariable("TXT_PRECONDITIONS", $lng->txt("preconditions"));
			$this->tpl->parseCurrentBlock();
		}
	}

	/**
	* insert command button
	*
	* @access	private
	* @param	string		$a_href		link url target
	* @param	string		$a_text		link text
	* @param	string		$a_frame	link frame target
	*/
	function insertCommand($a_href, $a_text, $a_frame = "")
	{
		if ($a_frame != "")
		{
			$this->tpl->setCurrentBlock("item_frame");
			$this->tpl->setVariable("TARGET_COMMAND", $a_frame);
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setCurrentBlock("item_command");
		$this->tpl->setVariable("HREF_COMMAND", $a_href);
		$this->tpl->setVariable("TXT_COMMAND", $a_text);
		$this->tpl->parseCurrentBlock();
	}

	/**
	* insert cut command
	*
	* @access	private
	* @param	object		$a_tpl		template object
	* @param	int			$a_ref_id	item reference id
	*/
	function insertDeleteCommand()
	{
		if ($this->std_cmd_only)
		{
			return;
		}
		if ($this->rbacsystem->checkAccess("delete", $this->ref_id))
		{
			$this->ctrl->setParameter($this->container_obj, "ref_id",
				$this->container_obj->object->getRefId());
			$this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->ref_id);
			$cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "delete");
			$this->insertCommand($cmd_link, $this->lng->txt("delete"));
			$this->adm_commands_included = true;
		}
	}

	/**
	* insert link command
	*
	* @access	private
	* @param	object		$a_tpl		template object
	* @param	int			$a_ref_id	item reference id
	*/
	function insertLinkCommand()
	{
		if ($this->std_cmd_only)
		{
			return;
		}
		// if the permission is changed here, it  has
		// also to be changed in ilContainerGUI, admin command check
		if ($this->rbacsystem->checkAccess("delete", $this->ref_id))
		{
			$this->ctrl->setParameter($this->container_obj, "ref_id",
				$this->container_obj->object->getRefId());
			$this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->ref_id);
			$cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "link");
			$this->insertCommand($cmd_link, $this->lng->txt("link"));
			$this->adm_commands_included = true;
		}
	}

	/**
	* insert cut command
	*
	* @access	private
	* @param	object		$a_tpl		template object
	* @param	int			$a_ref_id	item reference id
	*/
	function insertCutCommand()
	{
		if ($this->std_cmd_only)
		{
			return;
		}
		// if the permission is changed here, it  has
		// also to be changed in ilContainerGUI, admin command check
		if ($this->rbacsystem->checkAccess("delete", $this->ref_id))
		{
			$this->ctrl->setParameter($this->container_obj, "ref_id",
				$this->container_obj->object->getRefId());
			$this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->ref_id);
			$cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "cut");
			$this->insertCommand($cmd_link, $this->lng->txt("move"));
			$this->adm_commands_included = true;
		}
	}

	/**
	* insert subscribe command
	*
	* @access	private
	* @param	object		$a_tpl		template object
	* @param	int			$a_ref_id	item reference id
	*/
	function insertSubscribeCommand()
	{
		if ($this->std_cmd_only)
		{
			return;
		}
		if ($this->ilias->account->getId() != ANONYMOUS_USER_ID)
		{
			if (!$this->ilias->account->isDesktopItem($this->ref_id, $this->type))
			{
				if ($this->rbacsystem->checkAccess("read", $this->ref_id)
					&& is_object($this->container_obj))
				{
					$this->ctrl->setParameter($this->container_obj, "ref_id",
						$this->container_obj->object->getRefId());
					$this->ctrl->setParameter($this->container_obj, "type", $this->type);
					$this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->ref_id);
					$cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "addToDesk");
					$this->insertCommand($cmd_link, $this->lng->txt("to_desktop"));
				}
			}
			else
			{
				// not so nice, if no container object given, it must
				// be personal desktop
				if (is_object($this->container_obj))
				{
					$this->ctrl->setParameter($this->container_obj, "ref_id",
						$this->container_obj->object->getRefId());
					$this->ctrl->setParameter($this->container_obj, "type", $this->type);
					$this->ctrl->setParameter($this->container_obj, "item_ref_id", $this->ref_id);
					$cmd_link = $this->ctrl->getLinkTarget($this->container_obj, "removeFromDesk");
					$this->insertCommand($cmd_link, $this->lng->txt("unsubscribe"));
				}
				else
				{
					$this->ctrl->setParameterByClass("ilpersonaldesktopgui", "type", $this->type);
					$this->ctrl->setParameterByClass("ilpersonaldesktopgui", "item_ref_id", $this->ref_id);
					$cmd_link = $this->ctrl->getLinkTargetByClass("ilpersonaldesktopgui",
						"dropItem");
					$this->insertCommand($cmd_link, $this->lng->txt("unsubscribe"));
				}
			}
		}
	}

	/**
	* insert info screen command
	*
	*/
	function insertInfoScreenCommand()
	{
		if ($this->std_cmd_only)
		{
			return;
		}
		$cmd_link = $this->getCommandLink("infoScreen");
		$cmd_frame = $this->getCommandFrame("infoScreen");
		$this->insertCommand($cmd_link, $this->lng->txt("info_short"), $cmd_frame);
	}

	/**
	* insert all commands into html code
	*
	* @access	private
	* @param	object		$a_tpl		template object
	* @param	int			$a_ref_id	item reference id
	*/
	function insertCommands()
	{
		if (!$this->getCommandsStatus())
		{
			return;
		}
		$this->ctrl->setParameterByClass($this->gui_class_name, "ref_id", $this->ref_id);

		$commands = $this->getCommands($this->ref_id, $this->obj_id);

		$this->default_command = false;
		
		foreach($commands as $command)
		{
			if ($command["granted"] == true )
			{
				if (!$command["default"] === true)
				{
					if (!$this->std_cmd_only)
					{
						// workaround for repository frameset
						$command["link"] = 
							$this->appendRepositoryFrameParameter($command["link"]);
						
						$cmd_link = $command["link"];
						$this->insertCommand($cmd_link, $this->lng->txt($command["lang_var"]),
							$command["frame"]);
					}
				}
				else
				{
					$this->default_command = $this->createDefaultCommand($command);
					//$this->default_command = $command;
				}
			}
		}

		// custom commands
		if (is_array($this->cust_commands))
		{
			foreach ($this->cust_commands as $command)
			{
				$this->insertCommand($command["link"], $this->lng->txt($command["lang_var"]),
					$command["frame"]);
			}
		}

		// info screen commmand
		if ($this->getInfoScreenStatus())
		{
			$this->insertInfoScreenCommand();
		}

		if (!$this->isMode(IL_LIST_AS_TRIGGER))
		{
			// delete
			if ($this->delete_enabled)
			{
				$this->insertDeleteCommand();
			}

			// link
			if ($this->link_enabled)
			{
				$this->insertLinkCommand();
			}

			// cut
			if ($this->cut_enabled)
			{
				$this->insertCutCommand();
			}

			// subscribe
			if ($this->subscribe_enabled)
			{
				$this->insertSubscribeCommand();
			}
		}
	}

	/**
	* workaround: all links into the repository (from outside)
	* must tell repository to setup the frameset
	*/
	function appendRepositoryFrameParameter($a_link)
	{
		$script = substr(strrchr($_SERVER["PHP_SELF"],"/"),1);

		if (substr($script,0,14) != "repository.php" &&
			is_int(strpos($a_link,"repository.php")))
		{
			if ($this->type != "frm")
			{
				$a_link = 
					ilUtil::appendUrlParameterString($a_link, "rep_frame=1");
			}
		}
		
		return $a_link;
	}

	/**
	* insert path
	*/
	function insertPath()
	{
		global $tree, $lng;

		if ($this->getPathStatus() != false)
		{
			$path = $tree->getPathId($this->ref_id);
			$sep = false;
			unset($path[count($path) - 1]);
			unset($path[0]);
			foreach ($path as $id)
			{
				$this->tpl->setCurrentBlock("path_item");
				if ($sep)
				{
					$this->tpl->setVariable("SEPARATOR", " > ");
				}
				$this->tpl->setVariable("PATH_ITEM",
					ilObject::_lookupTitle(ilObject::_lookupObjId($id)));
				$this->tpl->parseCurrentBlock();
				$sep = true;
			}
			$this->tpl->setCurrentBlock("path");
			$this->tpl->setVariable("TXT_LOCATION", $lng->txt("locator"));
			$this->tpl->parseCurrentBlock();
		}
	}
	
	/**
	* returns whether any admin commands (link, delete, cut)
	* are included in the output
	*/
	function adminCommandsIncluded()
	{
		return $this->adm_commands_included;
	}

	/**
	* Get all item information (title, commands, description) in HTML
	*
	* @access	public
	* @param	int			$a_ref_id		item reference id
	* @param	int			$a_obj_id		item object id
	* @param	int			$a_title		item title
	* @param	int			$a_description	item description
	* @return	string		html code
	*/
	function getListItemHTML($a_ref_id, $a_obj_id, $a_title, $a_description)
	{
		global $ilAccess, $ilBench;
		
		// this variable stores wheter any admin commands
		// are included in the output
		$this->adm_commands_included = false;

		// only for permformance exploration
		$type = ilObject::_lookupType($a_obj_id);

		// initialization
		$ilBench->start("ilObjectListGUI", "1000_getListHTML_init$type");
		$this->tpl =& new ilTemplate ("tpl.container_list_item.html", true, true);
		$this->initItem($a_ref_id, $a_obj_id, $a_title, $a_description);
		$ilBench->stop("ilObjectListGUI", "1000_getListHTML_init$type");

  		// visible check
		$ilBench->start("ilObjectListGUI", "2000_getListHTML_check_visible");
		if (!$ilAccess->checkAccess("visible", "", $a_ref_id, "", $a_obj_id))
		{
			$ilBench->stop("ilObjectListGUI", "2000_getListHTML_check_visible");
			return "";
		}
		$ilBench->stop("ilObjectListGUI", "2000_getListHTML_check_visible");

		// commands
		$ilBench->start("ilObjectListGUI", "4000_insert_commands");
		$this->insertCommands();
		$ilBench->stop("ilObjectListGUI", "4000_insert_commands");

		// payment
		$ilBench->start("ilObjectListGUI", "5000_insert_pay");
		$this->insertPayment();
		$ilBench->stop("ilObjectListGUI", "5000_insert_pay");
		
		if ($this->getCommandsStatus() || $this->payment_enabled)
		{
			$this->tpl->touchBlock("command_block");
		}

		// insert title and describtion
		$ilBench->start("ilObjectListGUI", "3000_insert_title_desc");
		$this->insertTitle();
		if (!$this->isMode(IL_LIST_AS_TRIGGER))
		{
			if ($this->getDescriptionStatus())
			{
				$this->insertDescription();
			}
		}
		$ilBench->stop("ilObjectListGUI", "3000_insert_title_desc");

		// properties
		$ilBench->start("ilObjectListGUI", "6000_insert_properties$type");
		if ($this->getPropertiesStatus())
		{
			$this->insertProperties();
		}
		$ilBench->stop("ilObjectListGUI", "6000_insert_properties$type");

		// notice properties
		$ilBench->start("ilObjectListGUI", "6500_insert_notice_properties$type");
		if($this->getNoticePropertiesStatus())
		{
			$this->insertNoticeProperties();
		}
		$ilBench->stop("ilObjectListGUI", "6500_insert_properties$type");

		// preconditions
		$ilBench->start("ilObjectListGUI", "7000_insert_preconditions");
		if ($this->getPreconditionsStatus())
		{
			$this->insertPreconditions();
		}
		$ilBench->stop("ilObjectListGUI", "7000_insert_preconditions");

		// path
		$ilBench->start("ilObjectListGUI", "8000_insert_path");
		$this->insertPath();
		$ilBench->stop("ilObjectListGUI", "8000_insert_path");

		// reset properties and commands
		$this->cust_prop = array();
		$this->cust_commands = array();

		return $this->tpl->get();
	}
	
	/**
	* Returns whether current item is a block in a side column or not
	*/
	function isSideBlock()
	{
		return false;
	}

} // END class.ilObjectListGUI
?>
