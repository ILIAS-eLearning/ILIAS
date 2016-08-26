<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * TableGUI class for user action administration
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesUser
 */
class ilUserActionAdminTableGUI extends ilTable2GUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;


	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_context_comp, $a_context_id)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->tpl = $DIC["tpl"];

		$this->context_comp = $a_context_comp;
		$this->context_id = $a_context_id;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);

		include_once("./Services/User/Actions/classes/class.ilUserActionProviderFactory.php");
		$data = array();
		foreach(ilUserActionProviderFactory::getAllProviders() as $p)
		{
			foreach ($p->getActionTypes() as $id => $name)
			{
				$data[] = array(
					"action_comp_id" => $p->getComponentId(),
					"action_type_id" => $id,
					"action_type_name" => $name
				);
			}
		}

		$this->setData($data);
		$this->setTitle($this->lng->txt(""));

		$this->addColumn($this->lng->txt("user_action"));
		$this->addColumn($this->lng->txt("active"), "", "1");
		
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.user_action_admin_row.html", "Services/User/Actions");

		//$this->addMultiCommand("", $this->lng->txt(""));
		$this->addCommandButton("save", $this->lng->txt("save"));
	}
	
	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		$this->tpl->setVariable("VAL", $a_set["action_type_name"]);
	}

}
?>