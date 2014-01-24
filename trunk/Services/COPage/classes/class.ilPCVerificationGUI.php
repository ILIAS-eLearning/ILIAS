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

require_once("./Services/COPage/classes/class.ilPCVerification.php");
require_once("./Services/COPage/classes/class.ilPageContentGUI.php");

/**
* Class ilPCVerificationGUI
*
* Handles user commands on verifications
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $I$
*
* @ingroup ServicesCOPage
*/
class ilPCVerificationGUI extends ilPageContentGUI
{

	/**
	* Constructor
	* @access	public
	*/
	function ilPCVerificationGUI(&$a_pg_obj, &$a_content_obj, $a_hier_id, $a_pc_id = "")
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
	 * Insert new verification form.
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
	 * Edit verification form.
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
	 * Init verification form
	 *
	 * @param bool $a_insert
	 * @return ilPropertyFormGUI
	 */
	protected function initForm($a_insert = false)
	{
		global $ilCtrl, $ilUser, $lng;

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		if ($a_insert)
		{
			$form->setTitle($this->lng->txt("cont_insert_verification"));
		}
		else
		{
			$form->setTitle($this->lng->txt("cont_update_verification"));
		}

		$lng->loadLanguageModule("wsp");
		$options = array();
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		$tree = new ilWorkspaceTree($ilUser->getId());
		$root = $tree->getRootId();
		if($root)
		{
			$root = $tree->getNodeData($root);
			foreach ($tree->getSubTree($root) as $node)
			{
				if (in_array($node["type"], array("excv", "tstv", "crsv", "scov")))
				{
					$options[$node["obj_id"]] = $node["title"]." (".$lng->txt("wsp_type_".$node["type"]).")";
				}
			}
			asort($options);		
		}	
		$obj = new ilSelectInputGUI($this->lng->txt("cont_verification_object"), "object");
		$obj->setRequired(true);
		$obj->setOptions($options);
		$form->addItem($obj);

		if ($a_insert)
		{
			
			$form->addCommandButton("create_verification", $this->lng->txt("save"));
			$form->addCommandButton("cancelCreate", $this->lng->txt("cancel"));
		}
		else
		{
			$data = $this->content_obj->getData();
			$obj->setValue($data["id"]);
			
			$form->addCommandButton("update", $this->lng->txt("save"));
			$form->addCommandButton("cancelUpdate", $this->lng->txt("cancel"));
		}

		return $form;
	}

	/**
	* Create new verification
	*/
	function create()
	{
		$form = $this->initForm(true);
		if($form->checkInput())
		{
			$type = ilObject::_lookupType($form->getInput("object"));
			if($type)
			{			
				$this->content_obj = new ilPCVerification($this->getPage());
				$this->content_obj->create($this->pg_obj, $this->hier_id, $this->pc_id);
				$this->content_obj->setData($type, $form->getInput("object"));
				$this->updated = $this->pg_obj->update();
				if ($this->updated === true)
				{
					$this->ctrl->returnToParent($this, "jump".$this->hier_id);
				}
			}
		}

		$this->insert($form);
	}

	/**
	* Update verification
	*/
	function update()
	{
		$form = $this->initForm(true);
		if($form->checkInput())
		{
			$type = ilObject::_lookupType($form->getInput("object"));
			if($type)
			{	
				$this->content_obj->setData($type, $form->getInput("object"));
				$this->updated = $this->pg_obj->update();
				if ($this->updated === true)
				{
					$this->ctrl->returnToParent($this, "jump".$this->hier_id);
				}
			}
		}

		$this->pg_obj->addHierIDs();
		$this->edit($form);
	}
}

?>