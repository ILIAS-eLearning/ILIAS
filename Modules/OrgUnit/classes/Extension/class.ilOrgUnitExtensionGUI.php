<?php

require_once "Services/Repository/classes/class.ilObjectPluginGUI.php";
require_once "Modules/OrgUnit/classes/class.ilObjOrgUnit.php";
require_once "Modules/OrgUnit/classes/class.ilOrgUnitExplorerGUI.php";
require_once("./Modules/OrgUnit/classes/Extension/class.ilOrgUnitObjectPlugin.php");

abstract class ilOrgUnitExtensionGUI extends ilObjectPluginGUI {

	/**
	 * @var ilLocatorGUI
	 */
	protected $ilLocator;

	public function __construct($a_ref_id = 0, $a_id_type = self::REPOSITORY_NODE_ID, $a_parent_node_id = 0) {
		global $ilLocator;
		parent::__construct($a_ref_id, $a_id_type, $a_parent_node_id);
		$this->ilLocator = $ilLocator;
		$this->showTree();
	}

	/**
	 * Get plugin object
	 * @return object plugin object
	 * @throws ilPluginException
	 */
	protected function getPlugin()
	{
		if(!$this->plugin) {
			$this->plugin =
				ilPlugin::getPluginObject(IL_COMP_MODULE, "OrgUnit", "orguext",
					ilPlugin::lookupNameForId(IL_COMP_MODULE, "OrgUnit", "orguext", $this->getType()));
			if (!is_object($this->plugin)) {
				throw new ilPluginException("ilObjectPluginGUI: Could not instantiate plugin object for type " . $this->getType() . ".");
			}
		}
		return $this->plugin;
	}

	/*
	 *
	 */
	protected function setLocator() {
		global $tpl;
		$path = $this->tree->getPathFull($_GET["ref_id"], ilObjOrgUnit::getRootOrgRefId());
		// add item for each node on path
		foreach ((array)$path as $key => $row) {
			if ($row["title"] == "__OrgUnitAdministration") {
				$row["title"] = $this->lng->txt("objs_orgu");
			}
			$this->ctrl->setParameterByClass("ilobjorgunitgui", "ref_id", $row["child"]);
			$this->ilLocator->addItem($row["title"], $this->ctrl->getLinkTargetByClass(array("iladministrationgui", "ilobjorgunitgui"), "view"), ilFrameTargetInfo::_getFrame("MainContent"), $row["child"]);
			$this->ctrl->setParameterByClass("ilobjplugindispatchgui", "ref_id", $_GET["ref_id"]);
		}
		$tpl->setLocator();
	}

	public function showTree() {
		$this->ctrl->setParameterByClass("ilObjPluginDispatchGUI", "ref_id", $_GET["ref_id"]);
		$this->ctrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $_GET["ref_id"]);
		$tree = new ilOrgUnitExplorerGUI("orgu_explorer", array("ilAdministrationGUI", "ilObjOrgUnitGUI"), "showTree", new ilTree(1));
		$tree->setTypeWhiteList(
			$this->getTreeWhiteList()
		);
		if (!$tree->handleCommand()) {
			$this->tpl->setLeftNavContent($tree->getHTML());
		}
	}

	protected function getTreeWhiteList() {
		$whiteList = array("orgu");
		$pls = ilOrgUnitObjectPluginGUI::getActivePluginIdsForTree();
		return array_merge($whiteList, $pls);
	}
}