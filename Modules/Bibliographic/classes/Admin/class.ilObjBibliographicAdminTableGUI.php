<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
/**
 * Bibliographic ilObjBibliographicAdminTableGUI
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 * @author  Martin Studer <ms@studer-raimann.ch>
 *
 *
 * @ingroup ModulesBibliographic
 */
require_once("./Services/Table/classes/class.ilTable2GUI.php");
require_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
class ilObjBibliographicAdminTableGUI extends ilTable2GUI {

	var $gui = NULL;


	/**
	 * Constructor
	 *
	 * @global ilLanguage           $lng
	 * @global ilCtrl2              $ilCtrl
	 *
	 * @param ilObjChatroomAdminGUI $a_ref
	 * @param string                $cmd
	 */
	public function __construct($a_ref, $cmd) {
		global $lng, $ilCtrl;
		parent::__construct($a_ref, $cmd);
		$this->gui = $a_ref;
		$this->setTitle($lng->txt('bibl_settings_libraries'));
		$this->setId('bibl_libraries_tbl');
		$this->addColumn($lng->txt('bibl_library_name'), '', '30%');
		$this->addColumn($lng->txt('bibl_library_url'), '' . '30%');
		$this->addColumn($lng->txt('bibl_library_img'), '', '30%');
		$this->addColumn($lng->txt('actions'), '', '8%');
		$this->addCommandButton('add', $this->lng->txt("add"));
		$this->setEnableNumInfo(false);
		$this->setFormAction($ilCtrl->getFormAction($a_ref));
		$this->setRowTemplate('tpl.bibl_settings_lib_list_row.html', 'Modules/Bibliographic');
	}


	/**
	 * Fills table rows with content from $a_set.
	 *
	 * @global ilCtrl2 $ilCtrl
	 *
	 * @param array    $a_set
	 */
	public function fillRow($a_set) {
		global $ilCtrl;
		$this->tpl->setVariable('VAL_LIBRARY_NAME', $a_set['name']);
		$this->tpl->setVariable('VAL_LIBRARY_URL', $a_set['url']);
		$this->tpl->setVariable('VAL_LIBRARY_IMG', $a_set['img']);
		$current_selection_list = new ilAdvancedSelectionListGUI();
		$current_selection_list->setListTitle($this->lng->txt("actions"));
		$current_selection_list->setId($a_set['id']);
		$current_selection_list->addItem($this->lng->txt("edit"), "",
			$ilCtrl->getLinkTarget($this->gui, 'edit') . "&lib_id=" . $a_set['id']);
		$current_selection_list->addItem($this->lng->txt("delete"), "",
			$ilCtrl->getLinkTarget($this->gui, 'delete') . "&lib_id=" . $a_set['id']);
		$this->tpl->setVariable('VAL_ACTIONS', $current_selection_list->getHTML());
	}
}