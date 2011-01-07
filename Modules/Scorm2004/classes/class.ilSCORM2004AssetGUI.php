<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/Scorm2004/classes/class.ilSCORM2004ScoGUI.php");
require_once("./Modules/Scorm2004/classes/class.ilSCORM2004Asset.php");

/**
 * Class ilSCORM2004AssetGUI
 *
 * User Interface for Scorm 2004 Asset Nodes
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilSCORM2004AssetGUI: ilMDEditorGUI, ilNoteGUI, ilPCQuestionGUI, ilSCORM2004PageGUI
 *
 * @ingroup ModulesScorm2004
 */
class ilSCORM2004AssetGUI extends ilSCORM2004ScoGUI
{
	/**
	 * Constructor
	 * @access	public
	 */
	function ilSCORM2004AssetGUI($a_slm_obj, $a_node_id = 0)
	{
		global $ilCtrl;

		$ilCtrl->saveParameter($this, "obj_id");
		$this->ctrl = $ilCtrl;

		parent::__construct($a_slm_obj, $a_node_id);
	}

	/**
	 * Get Node Type
	 */
	function getType()
	{
		return "ass";
	}
}
?>
