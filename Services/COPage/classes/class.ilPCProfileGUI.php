<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
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

require_once("./Services/COPage/classes/class.ilPCProfile.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCProfileGUI
*
* Handles user commands on personal data
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $I$
*
* @ingroup ServicesCOPage
*/
class ilPCProfileGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCProfileGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
	{
		parent::ilPageContentGUI($a_pg_obj, $a_content_obj, $a_hier_id, $a_pc_id);
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		// get next class that processes or forwards current command
		$next_class = $this->ctrl->getNextClass($this);

		// get current command
		$cmd = $this->ctrl->getCmd();

		switch($next_class)
		{
			default:
				$ret =& $this->$cmd();
				break;
		}

		return $ret;
	}

	/**
	 * Insert new personal data form.
	 *
	 * @param ilPropertyFormGUI $a_form
	 */
	function insert(ilPropertyFormGUI $a_form = null)
	{
		global $tpl;

		$this->displayValidationError();

		if(!$a_form)
		{
			$a_form = $this->initForm(true);
		}
		$tpl->setContent($a_form->getHTML());
	}

	/**
	 * Edit personal data form.
	 *
	 * @param ilPropertyFormGUI $a_form
	 */
	function edit(ilPropertyFormGUI $a_form = null)
	{
		global $tpl;

		$this->displayValidationError();

		if(!$a_form)
		{
			$a_form = $this->initForm();
		}
		$tpl->setContent($a_form->getHTML());
	}

	/**
	 * Init profile form
	 *
	 * @param bool $a_insert
	 * @return ilPropertyFormGUI
	 */
	protected function initForm($a_insert = false)
	{
		global $ilCtrl, $ilToolbar;
				
		$is_template = ($this->getPageConfig()->getEnablePCType("PlaceHolder"));
				
		if(!$is_template)
		{
			$ilToolbar->addButton($this->lng->txt("cont_edit_personal_data"), 
				$ilCtrl->getLinkTargetByClass("ilpersonaldesktopgui", "jumptoprofile"),
				"profile");		
			
			$lng_suffix = "";
		}
		else
		{
			$lng_suffix = "_template";
		}

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		if ($a_insert)
		{
			$form->setTitle($this->lng->txt("cont_insert_profile"));
		}
		else
		{
			$form->setTitle($this->lng->txt("cont_update_profile"));
		}

		$mode = new ilRadioGroupInputGUI($this->lng->txt("cont_profile_mode"), "mode");
		$form->addItem($mode);

		$mode_inherit = new ilRadioOption($this->lng->txt("cont_profile_mode_inherit"), "inherit");
		$mode_inherit->setInfo($this->lng->txt("cont_profile_mode".$lng_suffix."_inherit_info"));
		$mode->addOption($mode_inherit);

		$mode_manual = new ilRadioOption($this->lng->txt("cont_profile_mode_manual"), "manual");
		$mode_manual->setInfo($this->lng->txt("cont_profile_mode_manual_info"));
		$mode->addOption($mode_manual);

		$prefs = array();
		if ($a_insert)
		{
			$mode->setValue("inherit");
		}
		else
		{
			$mode_value = $this->content_obj->getMode();
			$mode->setValue($mode_value);

			$prefs = array();
			if($mode_value == "manual")
			{
				foreach($this->content_obj->getFields() as $name)
				{
					$prefs["public_".$name] = "y";
				}
			}
		}

		// always has to be set
		$im_arr = array("icq","yahoo","msn","aim","skype","jabber","voip");
		foreach ($im_arr as $im)
		{
			if(!isset($prefs["public_im_".$im]))
			{
				$prefs["public_im_".$im] = "n";
			}
		}
			
		include_once "Services/User/classes/class.ilPersonalProfileGUI.php";
		$profile = new ilPersonalProfileGUI();
		$profile->showPublicProfileFields($form, $prefs, $mode_manual, $is_template);

		if ($a_insert)
		{
			
			$form->addCommandButton("create_profile", $this->lng->txt("save"));
			$form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));
		}
		else
		{

			$form->addCommandButton("update", $this->lng->txt("save"));
			$form->addCommandButton("cancelUpdate", $this->lng->txt("cancel"));
		}

		return $form;
	}

	/**
	 * Gather field values
	 *
	 * @return array
	 */
	protected function getFieldsValues()
	{
		$fields = array();
		foreach($_POST as $name => $value)
		{
			if(substr($name, 0, 4) == "chk_")
			{
				if($value)
				{
					$fields[] = substr($name, 4);
				}
			}
		}
		return $fields;
	}

	/**
	* Create new personal data.
	*/
	function create()
	{
		$form = $this->initForm(true);
		if($form->checkInput())
		{
			$this->content_obj = new ilPCProfile($this->getPage());
			$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
			$this->content_obj->setFields($form->getInput("mode"),
				$this->getFieldsValues());
			$this->updated = $this->pg_obj->update();
			if ($this->updated === true)
			{
				$this->ctrl->returnToParent($this, "jump".$this->hier_id);
			}
		}

		$this->insert($form);
	}

	/**
	* Update personal data.
	*/
	function update()
	{
		$form = $this->initForm(true);
		if($form->checkInput())
		{
			$this->content_obj->setFields($form->getInput("mode"),
					$this->getFieldsValues());
			$this->updated = $this->pg_obj->update();
			if ($this->updated === true)
			{
				$this->ctrl->returnToParent($this, "jump".$this->hier_id);
			}
		}

		$this->pg_obj->addHierIDs();
		$this->edit($form);
	}
}

?>