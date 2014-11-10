<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Class ilDataCollectionField
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilDataBibliographicRecordListTableGUI extends ilTable2GUI {

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;


	/**
	 * @param ilObjBibliographicGUI $a_parent_obj
	 * @param string                $a_parent_cmd
	 */
	public function  __construct(ilObjBibliographicGUI $a_parent_obj, $a_parent_cmd) {
		global $lng, $ilCtrl;
		$this->setId('tbl_bibl_overview');
		$this->setPrefix('tbl_bibl_overview');
		$this->setFormName('tbl_bibl_overview');
		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->parent_obj = $a_parent_obj;
		$this->ctrl = $ilCtrl;
		//Number of records
		$this->setEnableNumInfo(true);
		$this->setShowRowsSelector(true);
		// paging
		//		$this->setLimit(15, 15);
		//No row titles

		$this->setEnableHeader(false);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate('tpl.bibliographic_record_table_row.html', 'Modules/Bibliographic');
		// enable sorting by alphabet -- therefore an unvisible column 'content' is added to the table, and the array-key 'content' is also delivered in setData
		$this->addColumn($lng->txt('a'), 'content', 'auto');
		$this->initData();
		$this->setOrderField('content');
		$this->setDefaultOrderField('content');
	}


	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		$il_obj_entry = ilBibliographicEntry::getInstance($this->parent_obj->object->getFiletype(), $a_set['entry_id']);
		$this->tpl->setVariable('SINGLE_ENTRY', $il_obj_entry->getOverwiew());
		//Detail-Link
		$this->ctrl->setParameter($this->parent_obj, ilObjBibliographicGUI::P_ENTRY_ID, $a_set['entry_id']);
		$this->tpl->setVariable('DETAIL_LINK', $this->ctrl->getLinkTarget($this->parent_obj, 'showDetails'));
		// generate/render links to libraries
		$settings = ilBibliographicSetting::getAll();
		$arr_library_link = array();
		foreach ($settings as $set) {
			if ($set->getShowInList()) {
				$arr_library_link[] = $set->getButton($this->parent_obj->object, $il_obj_entry);
			}
		}
		if (count($arr_library_link)) {
			$this->tpl->setVariable('LIBRARY_LINK', implode('<br/>', $arr_library_link));
		}
	}


	protected function initData() {
		$entries = array();
		foreach (ilBibliographicEntry::getAllEntries($this->parent_obj->object->getId()) as $entry) {
			$ilBibliographicEntry = ilBibliographicEntry::getInstance($this->parent_obj->object->getFiletype(), $entry['entry_id']);
			$entry['content'] = strip_tags($ilBibliographicEntry->getOverwiew());
			$entries[] = $entry;
		}
		$this->setData($entries);
	}
}

?>