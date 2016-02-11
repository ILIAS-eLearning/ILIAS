<?php

require_once 'Services/Repository/classes/class.ilObjectPluginGUI.php';
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
require_once 'Services/Form/classes/class.ilTextInputGUI.php';
require_once 'Services/Form/classes/class.ilTextAreaInputGUI.php';
require_once 'Services/CaTUIComponents/classes/class.catTitleGUI.php';
require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");
require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
/**
* @ilCtrl_isCalledBy ilObjBuildingBlockPoolGUI: ilRepositoryGUI, ilAdministrationGUI, ilObjPluginDispatchGUI
* @ilCtrl_Calls ilObjBuildingBlockPoolGUI: ilPermissionGUI, ilInfoScreenGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjBuildingBlockPoolGUI: ilCommonActionDispatcherGUI
*/

class ilObjBuildingBlockPoolGUI extends ilObjectPluginGUI {

	protected $gLng;
	protected $gCtrl;
	protected $gTpl;
	protected $gUser;
	protected $gLog;
	protected $gAccess;

	protected function afterConstructor() {
		global $lng, $ilCtrl, $tpl, $ilUser, $ilLog, $ilAccess, $ilTabs, $ilToolbar;
		$this->gLng = $lng;
		$this->gCtrl = $ilCtrl;
		$this->gTpl = $tpl;
		$this->gUser = $ilUser;
		$this->gLog = $ilLog;
		$this->gAccess = $ilAccess;
		$this->gTabs = $ilTabs;
		$this->gToolbar = $ilToolbar;
	}

	public function getType() {
		return 'xbbp';
	}

	public function setTabs() {
		// tab for the "show content" command
		if ($this->gAccess->checkAccess("read", "", $this->object->getRefId())) {
			$this->gTabs->addTab("content", $this->object->plugin->txt($this->getType()."_content"),
			$this->gCtrl->getLinkTarget($this, "showContent"));
		}

		// standard permission tab
		if ($this->gAccess->checkAccess("edit_permission", "", $this->object->getRefId())) {
			$this->addPermissionTab();
		}
	}

	/**
	* Besides usual report commands (exportXLS, view, ...) showMenu goes here
	*/
	public function performCommand() {
		$cmd = $this->gCtrl->getCmd("showContent");

		switch ($cmd) {
			case "cancelBuildingBlock":
			case "cancelImport":
			case "showContent":
				require_once("Services/GEV/Utils/classes/class.gevSettings.php");
				if($this->gAccess->checkAccess(gevSettings::EDIT_BUILDING_BLOCKS, "", $this->object->getRefId())) {
					$this->gTabs->activateTab("content");
					return $this->showContent();
				} else if($this->gAccess->checkAccess("read", "", $this->object->getRefId())) {
					$this->gTabs->activateTab("content");
					return $this->showContentNoEdit();
				}
				break;
			case "editBuildingBlock":
				if($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
					$this->gTabs->activateTab("content");
					require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BuildingBlockPool/classes/class.ilBuildingBlockEditGUI.php");
					$bb_edit = new ilBuildingBlockEditGUI($_GET["bb_id"], ilBuildingBlockEditGUI::EDIT_UNIT, $this);
					$this->gTpl->setContent($bb_edit->getHTML());
				} else {
					$ilUtil->sendInfo("No permisions");
					$this->gCtrl->redirect($this);
				}
				break;
			case "deleteBuildingBlock":
				if($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
					$this->gTabs->activateTab("content");
					require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BuildingBlockPool/classes/class.ilBuildingBlockEditGUI.php");
					$bb_edit = new ilBuildingBlockEditGUI($_GET["bb_id"], ilBuildingBlockEditGUI::DELETE_UNIT, $this);
					$this->gTpl->setContent($bb_edit->deleteConfirm());
					break;
				} else {
					$ilUtil->sendInfo("No permisions");
					$this->gCtrl->redirect($this);
				}
				break;
			case "deleteConfirmedBuildingBlock":
				if($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
					$this->gTabs->activateTab("content");
					require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BuildingBlockPool/classes/class.ilBuildingBlockEditGUI.php");
					$bb_edit = new ilBuildingBlockEditGUI($_GET["bb_id"], ilBuildingBlockEditGUI::DELETE_UNIT, $this);
					$bb_edit->delete();
					break;
				} else {
					$ilUtil->sendInfo("No permisions");
					$this->gCtrl->redirect($this);
				}
				break;
			case "saveBuildingBlock":
				if($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
					$this->gTabs->activateTab("content");
					require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BuildingBlockPool/classes/class.ilBuildingBlockEditGUI.php");
					$bb_edit = new ilBuildingBlockEditGUI($_GET["bb_id"], ilBuildingBlockEditGUI::SAVE_UNIT, $this);
					$this->gTpl->setContent($bb_edit->save());
					break;
				} else {
					$ilUtil->sendInfo("No permisions");
					$this->gCtrl->redirect($this);
				}
				break;
			case "updateBuildingBlock":
				if($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
					$this->gTabs->activateTab("content");
					require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BuildingBlockPool/classes/class.ilBuildingBlockEditGUI.php");
					$bb_edit = new ilBuildingBlockEditGUI($_GET["bb_id"], ilBuildingBlockEditGUI::UPDATE_UNIT, $this);
					$this->gTpl->setContent($bb_edit->update());
					break;
				} else {
					$ilUtil->sendInfo("No permisions");
					$this->gCtrl->redirect($this);
				}
				break;
			case "addBuildingBlock":
				if($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
					$this->gTabs->activateTab("content");
					require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BuildingBlockPool/classes/class.ilBuildingBlockEditGUI.php");
					$bb_edit = new ilBuildingBlockEditGUI($_GET["bb_id"], ilBuildingBlockEditGUI::NEW_UNIT, $this);
					$this->gTpl->setContent($bb_edit->getHtml());
					break;
				} else {
					$ilUtil->sendInfo("No permisions");
					$this->gCtrl->redirect($this);
				}
			case "importBuildingBlock":
				if($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
					$this->gTabs->activateTab("content");
					require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BuildingBlockPool/classes/class.ilBuildingBlockImportTableGUI.php");
					$this->gCtrl->setParameter($this,"cmd","importBuildingBlock");
					$bb_import = new ilBuildingBlockImportTableGUI($this);
					$bb_import->addCommandButton("importConfirmedBuildingBlock",$this->lng->txt("import"));
					$bb_import->addCommandButton("cancelImport",$this->lng->txt("cancel"));
					$this->gTpl->setContent($bb_import->getHtml());
					$this->gCtrl->setParameter($this,"cmd",null);
					break;
				} else {
					$ilUtil->sendInfo("No permisions");
					$this->gCtrl->redirect($this);
				}
			case "importConfirmedBuildingBlock":
				if(isset($_POST["bb_obj_id"]) && $this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
					$this->gTabs->activateTab("content");
					require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BuildingBlockPool/classes/class.ilBuildingBlockImportTableGUI.php");
					$bb_import = new ilBuildingBlockImportTableGUI($this);
					$bb_import->importConfirmed($_POST["bb_obj_id"]);
					$this->gTpl->setContent($bb_import->getHtml());
					break;
				} else {
					$ilUtil->sendInfo("No permisions");
					$this->gCtrl->redirect($this);
				}
			default:
				throw new ilException("Unknown Command '$cmd'.");
		}
	}

	public function getAfterCreationCmd() {
		return "showContent";
	}

	public function getStandardCmd() {
		return "showContent";
	}

	protected function showContent() {
		$add_building_bock_link = $this->gCtrl->getLinkTarget($this, "addBuildingBlock");
		$this->gToolbar->addButton( $this->lng->txt("rep_robj_xbbp_add_building_block"), $add_building_bock_link);

		require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BuildingBlockPool/classes/class.ilBuildingBlockTableGUI.php");
		$bb_table = new ilBuildingBlockTableGUI(array("pool_id"=>$this->object->getId()), $this);
		
		$bb_table->addCommandButton("importBuildingBlock",$this->lng->txt("rep_robj_xbbp_copy_building_block")); // TODO: set this properly

		$this->gTpl->setContent($bb_table->getHTML());
	}

	protected function showContentNoEdit() {
		require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BuildingBlockPool/classes/class.ilBuildingBlockTableGUI.php");
		$bb_table = new ilBuildingBlockTableGUI(array("pool_id"=>$this->object->getId()), $this, false);
		$this->gTpl->setContent($bb_table->getHTML());
	}
}