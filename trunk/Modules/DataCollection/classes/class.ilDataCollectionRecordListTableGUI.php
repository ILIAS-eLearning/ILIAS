<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Services/Table/classes/class.ilTable2GUI.php';
require_once 'class.ilDataCollectionRecordViewGUI.php';
require_once 'class.ilDataCollectionField.php';
require_once './Services/Tracking/classes/class.ilLPStatus.php';
require_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
require_once 'class.ilDataCollectionDatatype.php';

/**
 * Class ilDataCollectionField
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Marcel Raimann <mr@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDataCollectionRecordListTableGUI  extends ilTable2GUI
{

	private $table;

    /**
     * @var ilDataCollectionRecord[]
     */
    protected $object_data;
    protected $numeric_fields;

    protected $filter = array();

	/*
	 * __construct
	 */
	public function  __construct(ilDataCollectionRecordListGUI $a_parent_obj, $a_parent_cmd, ilDataCollectionTable $table)
	{
		global $lng, $ilCtrl;

		$this->setPrefix("dcl_record_list");
        $this->setFormName('record_list');
        $this->setId("dcl_record_list" . $table->getId());
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->table = $table;
        $this->parent_obj = $a_parent_obj;
		$this->setRowTemplate("tpl.record_list_row.html", "Modules/DataCollection");

        // Setup columns and sorting columns
		$this->addColumn("", "_front", "15px");
        $this->numeric_fields = array();
        foreach($this->table->getVisibleFields() as $field)
		{
			$title = $field->getTitle();
            $sort_field = $title;
            $this->addColumn($title, $sort_field);
            if($field->getLearningProgress()){
				$this->addColumn($lng->txt("dcl_status"), "_status_".$field->getTitle());
			}
            if($field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_NUMBER) {
                $this->numeric_fields[] = $title;
            }
        }
		$this->addColumn($lng->txt("actions"), "", 	 "30px");

		$this->setTopCommands(true);
		$this->setEnableHeader(true);
		$this->setShowRowsSelector(true);
		$this->setShowTemplates(true);
		$this->setEnableHeader(true);
		$this->setEnableTitle(true);
		$this->setDefaultOrderDirection("asc");
        $this->setDefaultOrderField('id');
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, "applyFilter"));
		$this->initFilter();
        $this->setStyle('table', $this->getStyle('table') . ' ' . 'dcl_record_list');
    }

    /**
     * Return array of fields that are currently stored in the filter. Return empty array if no filtering is required.
     * @return array
     */
    public function getFilter() {
        return $this->filter;
    }

    public function setRecordData($data) {
        $this->object_data = $data;
        $this->buildData($data);
    }

	/*
	 * fillHeaderExcel
	 */
	public function fillHeaderExcel($worksheet, &$row)
	{
		$this->writeFilterToSession();
		$this->initFilter();
		$col = 0;
		
		foreach($this->table->getFields() as $field)
		{
			if($field->getExportable())
			{
				$worksheet->writeString($row, $col, $field->getTitle());
				$col++;
			}
		}
	}

    public function numericOrdering($field){
        return in_array($field, $this->numeric_fields);
    }

    /**
     * Parse data from record objects to an array that is then set to this table with ::setData()
     */
    private function buildData(){
        global $ilCtrl, $lng;

        $data = array();
        foreach($this->object_data as $record){
            $record_data = array();
            $record_data["_front"] = null;
            // The record object is only needed if we are exporting the data, see fillRowExcel() method
            $record_data['_record'] = ($ilCtrl->getCmd() == 'exportExcel') ? $record : null;

            foreach($this->table->getVisibleFields() as $field)
            {
                $title = $field->getTitle();
                //Check Options of Displaying
                $options = array();
                $arr_properties = $field->getProperties();
                if ($arr_properties[ilDataCollectionField::PROPERTYID_REFERENCE_LINK]) {
                    $options['link']['display'] = true;
                }
                if ($arr_properties[ilDataCollectionField::PROPERTYID_ILIAS_REFERENCE_LINK]) {
                    $options['link']['display'] = true;
                }
                $record_data[$title] = $record->getRecordFieldHTML($field->getId(), $options);

                // Additional column filled in ::fillRow() method, showing the learning progress
                if ($field->getLearningProgress()) {
                    $record_data["_status_".$title] = $this->getStatus($record, $field);
                }
            }

            $ilCtrl->setParameterByClass("ildatacollectionfieldeditgui", "record_id", $record->getId());
            $ilCtrl->setParameterByClass("ildatacollectionrecordviewgui", "record_id", $record->getId());
            $ilCtrl->setParameterByClass("ildatacollectionrecordeditgui","record_id", $record->getId());

            include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");

            if(ilDataCollectionRecordViewGUI::_getViewDefinitionId($record))
            {
                $record_data["_front"] = $ilCtrl->getLinkTargetByClass("ildatacollectionrecordviewgui", 'renderRecord');
            }

            $alist = new ilAdvancedSelectionListGUI();
            $alist->setId($record->getId());
            $alist->setListTitle($lng->txt("actions"));

            if(ilDataCollectionRecordViewGUI::_getViewDefinitionId($record))
            {
                $alist->addItem($lng->txt('view'), 'view', $ilCtrl->getLinkTargetByClass("ildatacollectionrecordviewgui", 'renderRecord'));
            }

            if($record->hasPermissionToEdit($this->parent_obj->parent_obj->ref_id))
            {
                $alist->addItem($lng->txt('edit'), 'edit', $ilCtrl->getLinkTargetByClass("ildatacollectionrecordeditgui", 'edit'));
            }

            if($record->hasPermissionToDelete($this->parent_obj->parent_obj->ref_id))
            {
                $alist->addItem($lng->txt('delete'), 'delete', $ilCtrl->getLinkTargetByClass("ildatacollectionrecordeditgui", 'confirmDelete'));
            }

            $record_data["_actions"] = $alist->getHTML();
            $data[] = $record_data;
        }
        $this->setData($data);
    }
	
	
	/*
	 * fillRowExcel
	 */
	public function fillRowExcel($worksheet, &$row, $record)
	{
		$col = 0;
		foreach($this->table->getFields() as $field)
		{
			if($field->getExportable())
			{
				$worksheet->writeString($row, $col, $record["_record"]->getRecordFieldExportValue($field->getId()));
				$col++;
			}
		}
	}
	
	/**
	 * fill row 
	 *
	 * @access public
	 * @param $a_set
	 */
	public function fillRow($record_data)
	{
		foreach($this->table->getVisibleFields() as $field)
		{
            $title = $field->getTitle();
			$this->tpl->setCurrentBlock("field");
            $content = $record_data[$title];
            if ($content === false || $content === null) $content = ''; // SW - This ensures to display also zeros in the table...
            $this->tpl->setVariable("CONTENT", $content);
			$this->tpl->parseCurrentBlock();
			if($field->getLearningProgress()){
                $this->tpl->setCurrentBlock("field");
			    $this->tpl->setVariable("CONTENT", $record_data["_status_".$title]);
                $this->tpl->parseCurrentBlock();
            }
		}

		if ($record_data["_front"])
		{
			$this->tpl->setVariable("VIEW_IMAGE_LINK", $record_data["_front"]);
			$this->tpl->setVariable("VIEW_IMAGE_SRC", ilUtil::img(ilUtil::getImagePath("cmd_view_s.png")));
		}
		$this->tpl->setVariable("ACTIONS", $record_data["_actions"]);

        return true;
	}

	/**
	 * This adds the collumn for status.
	 * @param ilDataCollectionRecord $record
	 * @param ilDataCollectionField $field
	 */
	private function getStatus(ilDataCollectionRecord $record, ilDataCollectionField $field){
		$record_field = ilDataCollectionCache::getRecordFieldCache($record, $field);
        $return = "";
        if($status = $record_field->getStatus()){
            $return = "<img src='".ilLearningProgressBaseGUI::_getImagePathForStatus($status->status)."'>";
        }
        return $return;
    }
	
	/*
	 * initFilter
	 */
	public function initFilter()
	{
		foreach($this->table->getFilterableFields() as $field) {
			$input = ilDataCollectionDatatype::addFilterInputFieldToTable($field, $this);
			$input->readFromSession();
			$value = $input->getValue();
            if (is_array($value)) {
                if ($value['from'] || $value['to']) {
                    $this->filter["filter_".$field->getId()] = $value;
                }
            } else {
                if ($value != '') {
                    $this->filter["filter_".$field->getId()] = $value;
                }
            }
		}
	}

}

?>