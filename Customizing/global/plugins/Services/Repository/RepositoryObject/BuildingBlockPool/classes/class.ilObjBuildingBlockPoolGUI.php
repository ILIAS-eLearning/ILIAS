<?php

require_once 'Services/Repository/classes/class.ilObjectPluginGUI.php';
require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
require_once 'Services/Form/classes/class.ilTextInputGUI.php';
require_once 'Services/Form/classes/class.ilTextAreaInputGUI.php';
require_once 'Services/CaTUIComponents/classes/class.catTitleGUI.php';
require_once("Services/CaTUIComponents/classes/class.catTableGUI.php");
require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");
require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BuildingBlockPool/classes/class.ilBuildingBlockEditGUI.php");
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

		// a "properties" tab
		if ($this->gAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$this->gTabs->addTab("properties", $this->object->plugin->txt("properties"), $this->gCtrl->getLinkTarget($this, "editProperties"));
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
				$bb_edit = $this->getBuildingBlockEditGUI(gevSettings::EDIT_BUILDING_BLOCKS, $_GET["bb_id"], ilBuildingBlockEditGUI::EDIT_UNIT);
				$this->gTpl->setContent($bb_edit->getHTML());
				break;
			case "deleteBuildingBlock":
				$bb_edit = $this->getBuildingBlockEditGUI(gevSettings::EDIT_BUILDING_BLOCKS, $_GET["bb_id"], ilBuildingBlockEditGUI::DELETE_UNIT);
				$this->gTpl->setContent($bb_edit->deleteConfirm());
				break;
			case "deleteConfirmedBuildingBlock":
				$bb_edit = $this->getBuildingBlockEditGUI(gevSettings::EDIT_BUILDING_BLOCKS, $_GET["bb_id"], ilBuildingBlockEditGUI::DELETE_UNIT);
				$bb_edit->delete();
				break;
			case "saveBuildingBlock":
				$bb_edit = $this->getBuildingBlockEditGUI(gevSettings::EDIT_BUILDING_BLOCKS, $_GET["bb_id"], ilBuildingBlockEditGUI::SAVE_UNIT);
				$this->gTpl->setContent($bb_edit->save());
				break;
			case "updateBuildingBlock":
				$bb_edit = $this->getBuildingBlockEditGUI(gevSettings::EDIT_BUILDING_BLOCKS, $_GET["bb_id"], ilBuildingBlockEditGUI::UPDATE_UNIT);
				$this->gTpl->setContent($bb_edit->update());
				break;
			case "addBuildingBlock":
				$bb_edit = $this->getBuildingBlockEditGUI(gevSettings::EDIT_BUILDING_BLOCKS, $_GET["bb_id"], ilBuildingBlockEditGUI::NEW_UNIT);
				$this->gTpl->setContent($bb_edit->getHtml());
				break;
			case "importBuildingBlock":
				if($this->gAccess->checkAccess(gevSettings::EDIT_BUILDING_BLOCKS, "", $this->object->getRefId())) {
					$this->gTabs->activateTab("content");
					require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BuildingBlockPool/classes/class.ilBuildingBlockImportTableGUI.php");
					$this->gCtrl->setParameter($this,"cmd","importBuildingBlock");
					$bb_import = new ilBuildingBlockImportTableGUI($this);
					$bb_import->addCommandButton("importConfirmedBuildingBlock",$this->lng->txt("rep_robj_xbbp_copy_building_block"));
					$bb_import->addCommandButton("cancelImport",$this->lng->txt("cancel"));
					$this->gTpl->setContent($bb_import->getHtml());
					$this->gCtrl->setParameter($this,"cmd",null);
					break;
				} else {
					ilUtil::sendInfo("No permisions");
					$this->gCtrl->redirect($this);
				}
			case "importConfirmedBuildingBlock":
				if(isset($_POST["bb_obj_id"]) && $this->gAccess->checkAccess(gevSettings::EDIT_BUILDING_BLOCKS, "", $this->object->getRefId())) {
					$this->gTabs->activateTab("content");
					require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BuildingBlockPool/classes/class.ilBuildingBlockImportTableGUI.php");
					$bb_import = new ilBuildingBlockImportTableGUI($this);
					$bb_import->importConfirmed($_POST["bb_obj_id"]);
					$this->gTpl->setContent($bb_import->getHtml());
					break;
				} else {
					ilUtil::sendInfo("No permisions");
					$this->gCtrl->redirect($this);
				}
			case "updateProperties":
				if($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
					$this->gTabs->activateTab("properties");
					$this->updateProperties();
				} else {
					ilUtil::sendInfo("No permisions");
					$this->gCtrl->redirect($this);
				}
				break;
			case "editProperties":
				if($this->gAccess->checkAccess("write", "", $this->object->getRefId())) {
					$this->gTabs->activateTab("properties");
					$this->editProperties();
				} else {
					ilUtil::sendInfo("No permisions");
					$this->gCtrl->redirect($this);
				}
				break;
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
		$bb_table = new ilBuildingBlockTableGUI(array("pool_id"=>$this->object->getId()), $this, true);
		
		$bb_table->addCommandButton("importBuildingBlock",$this->lng->txt("rep_robj_xbbp_copy_building_block")); // TODO: set this properly

		$this->gTpl->setContent($bb_table->getHTML());
	}

	protected function showContentNoEdit() {
		require_once("Customizing/global/plugins/Services/Repository/RepositoryObject/BuildingBlockPool/classes/class.ilBuildingBlockTableGUI.php");
		$bb_table = new ilBuildingBlockTableGUI(array("pool_id"=>$this->object->getId()), $this, false);
		$this->gTpl->setContent($bb_table->getHTML());
	}

	protected function getBuildingBlockEditGUI($permission, $bb_id, $bb_block_mode) {
		if($this->gAccess->checkAccess($permission, "", $this->object->getRefId())) {
			$this->gTabs->activateTab("content");
			$bb_edit = new ilBuildingBlockEditGUI($bb_id, $bb_block_mode, $this);
			return $bb_edit;
		} else {
			ilUtil::sendInfo("No permisions");
			$this->gCtrl->redirect($this);
		}
	}

	/**
	* Edit Properties. This commands uses the form class to display an input form.
	*/
	function editProperties()
	{
		$this->gTabs->activateTab("properties");
		$this->initPropertiesForm();
		$this->getPropertiesValues();
		$this->gTpl->setContent($this->form->getHTML());
	}
	
	/**
	* Init  form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initPropertiesForm()
	{
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		// title
		$ti = new ilTextInputGUI($this->txt("title"), "title");
		$ti->setRequired(true);
		$this->form->addItem($ti);

		// online
		$cb = new ilCheckboxInputGUI($this->gLng->txt("online"), "online");
		$this->form->addItem($cb);

		$this->form->addCommandButton("updateProperties", $this->txt("save"));

		$this->form->setTitle($this->txt("edit_properties"));
		$this->form->setFormAction($this->gCtrl->getFormAction($this));
	}
	
	/**
	* Get values for edit properties form
	*/
	function getPropertiesValues()
	{
		$values["title"] = $this->object->getTitle();
		$values["online"] = $this->object->getOnline();
		$this->form->setValuesByArray($values);
	}
	
	/**
	* Update properties
	*/
	public function updateProperties()
	{
		global $tpl, $lng, $ilCtrl;
	
		$this->initPropertiesForm();
		if ($this->form->checkInput())
		{
			$this->object->setTitle($this->form->getInput("title"));
			$this->object->setOnline($this->form->getInput("online"));
			$this->object->update();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "editProperties");
		}

		$this->form->setValuesByPost();
		$tpl->setContent($this->form->getHtml());
	}
}