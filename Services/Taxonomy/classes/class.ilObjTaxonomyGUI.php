<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";
require_once "./Services/Taxonomy/classes/class.ilObjTaxonomy.php";

/**
 * Taxonomy GUI class
 *
 * @author Alex Killing alex.killing@gmx.de 
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjTaxonomyGUI:
 *
 * @ingroup ServicesTaxonomy
 */
class ilObjTaxonomyGUI extends ilObject2GUI
{
	
	/**
	 * Execute command
	 */
	function __construct($a_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0)
	{
		parent::__construct($a_id, ilObject2GUI::OBJECT_ID);
	}
	
	/**
	 * Get type
	 *
	 * @return string type
	 */
	function getType()
	{
		return "tax";
	}

	/**
	 * Execute command
	 */
	function executeCommand()
	{
		global $ilCtrl, $ilUser, $ilTabs;
		
		$next_class = $ilCtrl->getNextClass();
		$cmd = $ilCtrl->getCmd();

		switch ($next_class)
		{
			default:
				$this->$cmd();
				break;
		}
	}
	
	/**
	 *
	 */
	protected function initCreationForms()
	{
		$forms = array();
		$forms[] = $this->initSingleUploadForm();
		$forms[] = $this->initZipUploadForm();
		
		return $forms;
	}

	/**
	 * save object
	 *
	 * @access	public
	 */
	function save()
	{
	}

	protected function afterSave(ilObject $a_new_object)
	{
	}
}
?>