<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
require_once("./Modules/DataCollection/classes/class.ilDataCollectionRecordViewViewdefinition.php");

/**
 * Class ilDataCollectionRecordViewViewdefinitionGUI
 *
 * @author       Martin Studer <ms@studer-raimann.ch>
 * @author       Marcel Raimann <mr@studer-raimann.ch>
 * @author       Fabian Schmid <fs@studer-raimann.ch>
 * @author       Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilDataCollectionRecordViewViewdefinitionGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMediaPoolTargetSelector
 * @ilCtrl_Calls ilDataCollectionRecordViewViewdefinitionGUI: ilPublicUserProfileGUI, ilPageObjectGUI
 */
class ilDataCollectionRecordViewViewdefinitionGUI extends ilPageObjectGUI {

	/**
	 * @var int
	 */
	protected $obj_id;
	/**
	 * @var int
	 */
	protected $table_id;
	/**
	 * @var ilCtrl
	 */
	public $ctrl;


	/**
	 * @param     $table_id
	 * @param int $a_definition_id
	 */
	public function __construct($table_id, $a_definition_id = 0) {
		global $tpl, $ilCtrl;
		/**
		 * @var $ilCtrl ilCtrl
		 */
		$this->ctrl = $ilCtrl;

		//TODO Permission-Check
		$this->table_id = $table_id;

		if (!$a_definition_id) {
			$a_definition_id = ilDataCollectionRecordViewViewdefinition::getIdByTableId($this->table_id);
		}

		// we always need a page object - create on demand
		if (!$a_definition_id) {
			$viewdef = new ilDataCollectionRecordViewViewdefinition();
			$viewdef->setTableId($this->table_id);
			$viewdef->setActive(false);
			$viewdef->create();
			$a_definition_id = $viewdef->getId();
		}

		parent::__construct("dclf", $a_definition_id);
		$this->getPageObject()->setTableId($this->table_id);

		// content style (using system defaults)
		include_once("./Services/Style/classes/class.ilObjStyleSheet.php");

		$tpl->setCurrentBlock("SyntaxStyle");
		$tpl->setVariable("LOCATION_SYNTAX_STYLESHEET", ilObjStyleSheet::getSyntaxStylePath());
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("ContentStyle");
		$tpl->setVariable("LOCATION_CONTENT_STYLESHEET", ilObjStyleSheet::getContentStylePath(0));
		$tpl->parseCurrentBlock();
	}


	/**
	 * execute command
	 */
	public function executeCommand() {
		global $ilLocator, $lng;

		$next_class = $this->ctrl->getNextClass($this);

		$viewdef = $this->getPageObject();
		if ($viewdef) {
			$this->ctrl->setParameter($this, "dclv", $viewdef->getId());
			$title = $lng->txt("dcl_view_viewdefinition");
		}

		switch ($next_class) {
			case "ilpageobjectgui":
				throw new ilCOPageException("Deprecated. ilDataCollectionRecordViewViewdefinitionGUI gui forwarding to ilpageobject");
			default:
				if ($viewdef) {
					$this->setPresentationTitle($title);
					$ilLocator->addItem($title, $this->ctrl->getLinkTarget($this, "preview"));
				}

				return parent::executeCommand();
		}
	}


	public function showPage() {
		global $lng, $ilToolbar;
		/**
		 * @var $ilToolbar ilToolbarGUI
		 */
		// :TODO: temporary legend of available placeholders
		if ($this->getOutputMode() == IL_PAGE_EDIT) {
			$delete_button = ilLinkButton::getInstance();
			$delete_button->setCaption('dcl_empty_view');
			$delete_button->setUrl($this->ctrl->getLinkTarget($this, 'confirmDelete'));
			$ilToolbar->addButtonInstance($delete_button);

			$activation_button = ilLinkButton::getInstance();
			if ($this->getPageObject()->getActive()) {
				$activation_button->setCaption('dcl_deactivate_view');
				$activation_button->setUrl($this->ctrl->getLinkTarget($this, 'deactivate'));
			} else {
				$activation_button->setCaption('dcl_activate_view');
				$activation_button->setUrl($this->ctrl->getLinkTarget($this, 'activate'));
			}

			$ilToolbar->addButtonInstance($activation_button);

			$legend = ilDataCollectionRecordViewViewdefinition::getAvailablePlaceholders($this->table_id);
			if (sizeof($legend)) {
				$this->setPrependingHtml("<span class=\"small\">" . $this->lng->txt("dcl_legend_placeholders") . ": " . implode(" ", $legend)
					. "</span>");
			}
		}

		return parent::showPage();
	}


	protected function activate() {
		$page = $this->getPageObject();
		$page->setActive(true);
		$page->update();
		$this->ctrl->redirect($this, 'edit');
	}


	protected function deactivate() {
		$page = $this->getPageObject();
		$page->setActive(false);
		$page->update();
		$this->ctrl->redirect($this, 'edit');
	}


	/**
	 * confirmDelete
	 */
	public function confirmDelete() {
		global $ilCtrl, $lng, $tpl;

		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$conf = new ilConfirmationGUI();
		$conf->setFormAction($ilCtrl->getFormAction($this));
		$conf->setHeaderText($lng->txt('dcl_confirm_delete_view_title'));

		$conf->addItem('table', (int)$this->table_id, $lng->txt('dcl_confirm_delete_view_text'));

		$conf->setConfirm($lng->txt('delete'), 'deleteView');
		$conf->setCancel($lng->txt('cancel'), 'cancelDelete');

		$tpl->setContent($conf->getHTML());
	}


	/**
	 * cancelDelete
	 */
	public function cancelDelete() {
		global $ilCtrl;

		$ilCtrl->redirect($this, "edit");
	}


	public function deleteView() {
		global $ilCtrl, $lng;

		if ($this->table_id && ilDataCollectionRecordViewViewdefinition::getIdByTableId($this->table_id)) {
			global $ilDB;
			$id = ilDataCollectionRecordViewViewdefinition::getIdByTableId($this->table_id);
			include_once("./Modules/DataCollection/classes/class.ilDataCollectionRecordViewViewdefinition.php");
			$pageObject = new ilDataCollectionRecordViewViewdefinition($id);
			$pageObject->delete();

			$query = "DELETE FROM il_dcl_view WHERE table_id = " . $this->table_id . " AND type = " . $ilDB->quote(0, "integer") . " AND formtype = "
				. $ilDB->quote(0, "integer");
			$ilDB->manipulate($query);
		}

		ilUtil::sendSuccess($lng->txt("dcl_empty_view_success"), true);

		$ilCtrl->redirectByClass("ilDataCollectionFieldListGUI", "listFields");
	}


	/**
	 * Finalizing output processing
	 *
	 * @param string $a_output
	 *
	 * @return string
	 */
	public function postOutputProcessing($a_output) {
		// You can use this to parse placeholders and the like before outputting

		// user view (IL_PAGE_PRESENTATION?)
		if ($this->getOutputMode() == IL_PAGE_PREVIEW) {
			include_once "Services/Form/classes/class.ilPropertyFormGUI.php";

			// :TODO: find a suitable presentation for matched placeholders
			$allp = ilDataCollectionRecordViewViewdefinition::getAvailablePlaceholders($this->table_id, true);
			foreach ($allp as $id => $item) {
				$parsed_item = new ilTextInputGUI("", "fields[" . $item->getId() . "]");
				$parsed_item = $parsed_item->getToolbarHTML();

				$a_output = str_replace($id, $item->getTitle() . ": " . $parsed_item, $a_output);
			}
		} // editor
		else {
			if ($this->getOutputMode() == IL_PAGE_EDIT) {
				$allp = ilDataCollectionRecordViewViewdefinition::getAvailablePlaceholders($this->table_id);

				// :TODO: find a suitable markup for matched placeholders
				foreach ($allp as $item) {
					$a_output = str_replace($item, "<span style=\"color:green\">" . $item . "</span>", $a_output);
				}
			}
		}

		return $a_output;
	}
}

?>