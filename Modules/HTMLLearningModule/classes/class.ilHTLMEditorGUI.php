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
* GUI class for learning module editor
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilHTLMEditorGUI: ilObjFileBasedLMGUI
*
* @ingroup ModulesHTMLLearningModule
*/
class ilHTLMEditorGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilRbacSystem
	 */
	protected $rbacsystem;

	/**
	 * @var ilErrorHandling
	 */
	protected $error;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilNavigationHistory
	 */
	protected $nav_history;

	var $tpl;
	var $lng;
	var $objDefinition;
	var $ref_id;

	/**
	* Constructor
	* @access	public
	*/
	function __construct()
	{
		global $DIC;

		$this->rbacsystem = $DIC->rbac()->system();
		$this->error = $DIC["ilErr"];
		$this->access = $DIC->access();
		$this->nav_history = $DIC["ilNavigationHistory"];
		$tpl = $DIC["tpl"];
		$lng = $DIC->language();
		$objDefinition = $DIC["objDefinition"];
		$ilCtrl = $DIC->ctrl();
		$rbacsystem = $DIC->rbac()->system();
		$ilErr = $DIC["ilErr"];
		
		$lng->loadLanguageModule("content");

		// check write permission
		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			$ilErr->raiseError($lng->txt("permission_denied"), $ilErr->MESSAGE);
		}

		$this->ctrl = $ilCtrl;

		//$this->ctrl->saveParameter($this, array("ref_id", "obj_id"));
		$this->ctrl->saveParameter($this, array("ref_id"));

		// initiate variables
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->objDefinition = $objDefinition;
		$this->ref_id = $_GET["ref_id"];

	}

	/**
	* execute command
	*/
	function executeCommand()
	{
		$tpl = $this->tpl;
		$ilCtrl = $this->ctrl;
		$ilAccess = $this->access;
		$ilNavigationHistory = $this->nav_history;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd("");

		// add entry to navigation history
		if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$ilCtrl->setParameterByClass("ilobjfilebasedlmgui", "ref_id", $_GET["ref_id"]);
			$ilNavigationHistory->addItem($_GET["ref_id"],
				$ilCtrl->getLinkTargetByClass(array("ilrepositorygui", "ilobjfilebasedlmgui"), "infoScreen"), "htlm");
		}

		switch($next_class)
		{
			case "ilobjfilebasedlmgui":
				require_once ("./Modules/HTMLLearningModule/classes/class.ilObjFileBasedLMGUI.php");
				$fblm_gui = new ilObjFileBasedLMGUI("", $_GET["ref_id"],true, false);
				$ilCtrl->forwardCommand($fblm_gui);
				$tpl->show();
				break;

			default:
				$this->ctrl->setCmdClass("ilobjfilebasedlmgui");
				$this->ctrl->setCmd("");
				return $this->executeCommand();
				break;
		}
	}

}
?>
