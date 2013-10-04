<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Class ilDataCollectionField
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 * @version $Id:
 *
 */
class ilDataBibliographicRecordListTableGUI  extends ilTable2GUI
{

	private $table;
	
	/*
	 * __construct
	 */
	public function  __construct(ilObjBibliographicGUI $a_parent_obj, $a_parent_cmd)
	{
		global $lng, $ilCtrl;

        $this->setId("tbl_bibl_overview");
        $this->setPrefix("tbl_bibl_overview");
        $this->setFormName('tbl_bibl_overview');

		parent::__construct($a_parent_obj, $a_parent_cmd);
	 	$this->parent_obj = $a_parent_obj;

        //Number of records
        $this->setEnableNumInfo(true);

        // paging
        $this->setLimit(15,15);

        //No row titles
		$this->setEnableHeader(false);

        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));

        $this->addColumn($lng->txt("title"), 'title', "auto");
        $this->setRowTemplate("tpl.bibliographic_record_table_row.html", "Modules/Bibliographic");

        //FIXME das ganze setzen des Textes allenfalls Auslagern in Model!
		$this->setData(ilBibliographicEntry::__getAllEntries($this->parent_obj->object->getId()));
	}


	/**
	 * fill row 
	 *
	 * @access public
	 * @param $a_set
	 */
	public function fillRow($a_set)
	{
		global $lng, $ilCtrl;

        $ilObjEntry = new ilBibliographicEntry($this->parent_obj->object->getFiletype(), $a_set['entry_id']);

        $this->tpl->setVariable("SINGLE_ENTRY", $ilObjEntry->getOverwiew());

        //Detail-Link
        $ilCtrl->setParameterByClass("ilObjBibliographicGUI", "entryId", $a_set['entry_id']);
        $this->tpl->setVariable("DETAIL_LINK", $ilCtrl->getLinkTargetByClass("ilObjBibliographicGUI", "showDetails"));
        $this->tpl->setVariable("VIEW_IMAGE_SRC", ilUtil::img(ilUtil::getImagePath("cmd_view_s.png")));
	}
}

?>