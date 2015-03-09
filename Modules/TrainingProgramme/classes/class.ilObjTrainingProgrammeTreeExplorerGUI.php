<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");
require_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
require_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
require_once("./Services/UIComponent/Button/classes/class.ilLinkButton.php");
require_once("./Modules/TrainingProgramme/classes/class.ilObjTrainingProgrammeSettingsGUI.php");
/**
 * Class ilTrainingProgrammeTreeGUI
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilObjTrainingProgrammeTreeExplorerGUI extends ilExplorerBaseGUI {
	protected $js_training_programme_path = "./Modules/TrainingProgramme/templates/js/ilTrainingProgramme.js";
	protected $css_training_programme_path = "./Modules/TrainingProgramme/templates/css/ilTrainingProgramme.css";

	/**
	 * @var array
	 */
	protected $stay_with_command = array( "", "render", "view", "infoScreen", "performPaste", "cut", "tree_view");

	/**
	 * @var int
	 */
	protected $tree_root_id;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;
	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @param $a_expl_id
	 * @param $a_parent_obj
	 * @param $a_parent_cmd
	 * @param $a_tree
	 */
	public function __construct($a_tree_root_id, $a_expl_id, $a_parent_obj, $a_parent_cmd) {
		global $ilAccess, $lng, $tpl, $ilToolbar, $ilCtrl;

		parent::__construct($a_expl_id, $a_parent_obj, $a_parent_cmd);

		$this->tree_root_id = $a_tree_root_id;

		$this->access = $ilAccess;
		$this->lng = $lng;
		$this->tpl = $tpl;
		$this->toolbar = $ilToolbar;
		$this->ctrl = $ilCtrl;

		$lng->loadLanguageModule("prg");

		$this->setAjax(true);

		if($this->checkAccess('write', $a_tree_root_id)) {
			$this->setEnableDnd(true);
		}
	}


	/**
	 * Return node element
	 *
	 * @param mixed $node
	 *
	 * @return string
	 */
	public function getNodeContent($node) {
		global $lng, $ilAccess;

		$node_classes = "title";
		if(($node->getRefId() == $_GET["ref_id"])) {
			$node_classes .= " ilHighlighted";
		}

		$data_line = '<span class="'.$node_classes.'">' . $node->getTitle() .'</span>';
		$data_line .= '<span class="points">'. $node->getPoints() .'</span>';

		if($this->checkAccess('write', $node->getRefId())) {
			$data_line .= '<span class="icon_bar">';
			$data_line .= $this->getActionLink('ilObjTrainingProgrammeSettingsGUI', 'view', array('ref_id'=>$node->getRefId()), ilGlyphGUI::get(ilGlyphGUI::INFO));
			$data_line .= $this->getActionLink('ilObjTrainingProgrammeTreeGUI', 'create', array('ref_id'=>$node->getRefId()), ilGlyphGUI::get(ilGlyphGUI::ADD));
			//$data_line .= $this->getActionLink('ilRepositoryGUI', 'create', array('ref_id'=>$node->getRefId(), 'new_type'=>'prg'), ilGlyphGUI::get(ilGlyphGUI::ADD), false);
			$data_line .= $this->getActionLink('ilObjTrainingProgrammeGUI', 'deleteObject', array('ref_id'=>$node->getRefId(), 'item_ref_id'=>$node->getRefId()), ilGlyphGUI::get(ilGlyphGUI::REMOVE));
			$data_line .= '</span>';
		}

		return $data_line;
	}


	/**
	 * Generate link-element
	 *
	 * @param      $target_class
	 * @param      $cmd
	 * @param      $params  url-params send to the
	 * @param      $content
	 * @param bool $async
	 *
	 * @return string
	 */
	protected function getActionLink($target_class, $cmd, $params, $content, $async = true) {
		foreach($params as $param_name=>$param_value) {
			$this->ctrl->setParameterByClass($target_class, $param_name, $param_value);
		}

		$props = ' class="button"';
		if($async) {
			$props .= '" data-toggle="modal" data-target="#settings_modal"';
		}

		return '<a href="'.$this->ctrl->getLinkTargetByClass($target_class, $cmd, '', true).'" '.$props.'>'.$content.'</a>';
	}



	/**
	 * @return mixed
	 */
	public function getRootNode() {
		$node = ilObjTrainingProgramme::getInstanceByRefId($this->tree_root_id);
		if($node->getRoot() != NULL) {
			return $node->getRoot();
		}
		return $node;
	}

	/**
	 * Get node icon
	 * Return custom icon of OrgUnit type if existing
	 *
	 * @param array $a_node
	 *
	 * @return string
	 */
	public function getNodeIcon($a_node) {
		global $ilias;
		//var_dump($a_node);

		$obj_id = ilObject::_lookupObjId($a_node->getRefId());
		if ($ilias->getSetting('custom_icons')) {
			//TODO: implement custom icon functionality
		}

		if(!$a_node->hasChildren()) {
			return ilObject::_getIcon($obj_id, "tiny");
		}
		return '';
	}


	/**
	 * @param mixed $node
	 *
	 * @return string
	 */
	public function getNodeHref($node) {
		global $ilCtrl;

		if ($ilCtrl->getCmd() == "performPaste") {
			$ilCtrl->setParameterByClass("ilObjTrainingProgrammeGUI", "target_node", $node->getRefId());
		}

		$ilCtrl->setParameterByClass("ilObjTrainingProgrammeGUI", "ref_id", $node->getRefId());

		return $this->getLinkTarget();
	}


	/**
	 * @return string
	 */
	protected function getLinkTarget() {
		global $ilCtrl;

		/*if ($ilCtrl->getCmdClass() == "ilobjtrainingprogrammegui" AND in_array($ilCtrl->getCmd(), $this->stay_with_command)) {
			return $ilCtrl->getLinkTargetByClass($ilCtrl->getCmdClass(), $ilCtrl->getCmd());
		} else {
			return $ilCtrl->getLinkTargetByClass("ilobjtrainingprogrammegui", "view");
		}*/
		return '#';
	}


	/**
	 * Get childs of node
	 *
	 * @param                  $a_parent_node_id
	 *
	 * @global ilAccessHandler $ilAccess
	 * @internal param int $a_parent_id parent id
	 * @return array childs
	 */
	public function getChildsOfNode($a_parent_node_id) {
		global $ilAccess;

		$parent = ilObjTrainingProgramme::getInstanceByRefId($a_parent_node_id);

		return $parent->getChildren();
	}


	/**
	 * Sort childs
	 *
	 * @param array $a_childs array of child nodes
	 * @param       $a_parent_node_id
	 *
	 * @internal param mixed $a_parent_node parent node
	 *
	 * @return array array of childs nodes
	 */
	public function sortChilds($a_childs, $a_parent_node_id) {
		usort($a_childs, array( __CLASS__, "sortbyTitle" ));

		return $a_childs;
	}


	/**
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	public function sortbyTitle($a, $b) {
		return strcmp($a->getTitle(), $b->getTitle());
	}


	/**
	 * Is node clickable?
	 *
	 * @param mixed            $a_node node object/array
	 *
	 * @global ilAccessHandler $ilAccess
	 * @return boolean node clickable true/false
	 */
	public function isNodeClickable($a_node) {
		if ($this->checkAccess('read', $a_node->getRefId())) {
			return true;
		}

		return false;
	}


	/**
	 * Get id of a node
	 *
	 * @param mixed $a_node node array or object
	 *
	 * @return string id of node
	 */
	function getNodeId($a_node) {
		if(!is_null($a_node)) {
			return $a_node->getRefId();
		}
		return null;
	}


	/**
	 * @return string
	 */
	public function getHTML() {
		$this->tpl->addJavascript($this->js_training_programme_path);
		$this->tpl->addCss($this->css_training_programme_path);

		$output = parent::getHTML();

		$this->tpl->addOnLoadCode('$("#'.$this->getContainerId().'").training_programme_tree('.');');

		return $output;
	}


	/**
	 * Helper method to check access
	 *
	 * @param $a_which
	 * @param $a_ref_id
	 *
	 * @return bool
	 */
	protected function checkAccess($a_which, $a_ref_id) {
		return $this->access->checkAccess($a_which, '', $a_ref_id);
	}
}

?>