<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Class ilDataCollectionField
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 */
class ilBibliographicRecordListTableGUI extends ilTable2GUI {

	use \ILIAS\Modules\OrgUnit\ARHelper\DIC;
	/**
	 * @var \ilBiblFactoryFacade
	 */
	protected $facade;
	/**
	 * @var \ilObjBibliographicGUI
	 */
	protected $parent_obj;


	/**
	 * ilBibliographicRecordListTableGUI constructor.
	 *
	 * @param \ilObjBibliographicGUI $a_parent_obj
	 * @param \ilBiblFactoryFacade   $facade
	 */
	public function __construct(ilObjBibliographicGUI $a_parent_obj, ilBiblFactoryFacade $facade) {
		$this->facade = $facade;
		$this->setId('tbl_bibl_overview');
		$this->setPrefix('tbl_bibl_overview');
		$this->setFormName('tbl_bibl_overview');
		parent::__construct($a_parent_obj);
		$this->parent_obj = $a_parent_obj;

		//Number of records
		$this->setEnableNumInfo(true);
		$this->setShowRowsSelector(true);

		$this->setEnableHeader(false);
		$this->setFormAction($this->ctrl()->getFormAction($a_parent_obj));
		$this->setRowTemplate('tpl.bibliographic_record_table_row.html', 'Modules/Bibliographic');
		// enable sorting by alphabet -- therefore an unvisible column 'content' is added to the table, and the array-key 'content' is also delivered in setData
		$this->addColumn($this->lng()->txt('a'), 'content', 'auto');
		$this->initFilter();
		$this->initData();
		$this->setOrderField('content');
		$this->setDefaultOrderField('content');
	}


	public function initFilter() {
		foreach ($this->facade->filterFactory()->getAllForObjectId($this->facade->iliasObject()
		                                                                        ->getId()) as $filter) {
			$filter = new ilBiblFieldFilterPresentationGUI($filter, $this->facade);
			$this->addFilterItem($filter->getFilterItem());
		}
	}


	/**
	 * @param array $a_set
	 */
	public function fillRow($a_set) {
		$il_obj_entry = ilBiblEntry::getInstance($this->parent_obj->object->getFileTypeAsString(), $a_set['entry_id']);
		$this->tpl->setVariable('SINGLE_ENTRY', ilBibliographicDetailsGUI::prepareLatex($il_obj_entry->getOverview()));
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
		foreach (ilBiblEntry::getAllEntries($this->parent_obj->object->getId()) as $entry) {
			$ilBibliographicEntry = ilBiblEntry::getInstance($this->parent_obj->object->getFileTypeAsString(), $entry['entry_id']);
			$entry['content'] = strip_tags($ilBibliographicEntry->getOverview());
			$entries[] = $entry;
		}
		$this->setData($entries);
	}
}
