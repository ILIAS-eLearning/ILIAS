<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once('./Services/ContainerReference/classes/class.ilContainerReferenceGUI.php');
/** 
* 
* 
* @author Fabian Wolf <wolf@leifos.com>
* @version $Id$
* 
* @ilCtrl_Calls ilObjGroupReferenceGUI: ilPermissionGUI, ilInfoScreenGUI
* @ingroup ModulesGroupReference
*/
class ilObjGroupReferenceGUI extends ilContainerReferenceGUI
{
	protected $target_type = 'grp';
	protected $reference_type = 'grpr';

	/**
	 * ilObjGroupReferenceGUI constructor.
	 * @param $a_data
	 * @param int $a_id
	 * @param bool $a_call_by_reference
	 * @param bool $a_prepare_output
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = false)
	{
		 parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{
		global $rbacsystem,$ilErr,$ilAccess;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();

		switch($next_class)
		{
			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$this->ctrl->forwardCommand(new ilPermissionGUI($this));
				break;

			default:
				if(!$cmd || $cmd == 'view')
				{
					$cmd = "edit";
				}
				$cmd .= "Object";
				$this->$cmd();
				break;
		}
		return true;
	}
	
	
	/**
	 * get tabs
	 *
	 * @access public
     * @param	object	tabs gui object
	 */
	public function getTabs()
	{
		global $ilAccess, $ilHelp;

		$ilHelp->setScreenIdComponent("grpr");

		if($ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("edit",
				$this->ctrl->getLinkTarget($this, "edit"),
				array(),
				"");
		}
		if ($ilAccess->checkAccess('edit_permission','',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), 
				array("perm","info","owner"), 'ilpermissiongui');
		}
	}

	/**
	 *  Support for goto php
	 *
	 * @param $a_target
	 */
	 public static function _goto($a_target)
	 {
		global $ilAccess, $ilErr, $lng;
		
		include_once('./Services/ContainerReference/classes/class.ilContainerReference.php');
		$target_ref_id = ilContainerReference::_lookupTargetRefId(ilObject::_lookupObjId($a_target));
		
		include_once('./Modules/Group/classes/class.ilObjGroupGUI.php');
		ilObjGroupGUI::_goto($target_ref_id);
	 }
		
	}