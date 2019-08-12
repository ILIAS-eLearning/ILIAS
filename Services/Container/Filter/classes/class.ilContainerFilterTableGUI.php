<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * Filter admin table
 *
 * @author @leifos.de
 *
 * @ingroup
 */
class ilContainerFilterTableGUI extends ilTable2GUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	/**
	 * @var ilContainerFilterService
	 */
	protected $container_filter_service;

	/**
	 * @var int
	 */
	protected $ref_id;

	/**
	 * Constructor
	 */
	function __construct(ilContainerFilterAdminGUI $a_parent_obj, string $a_parent_cmd, ilContainerFilterService $container_filter_service, ilObjCategory $cat)
	{
		global $DIC;

		$this->id = "t";
		$this->lng = $DIC->language();
		$this->ctrl = $DIC->ctrl();
		$this->container_filter_service = $container_filter_service;
		$this->ref_id = $cat->getRefId();

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setData($this->getItems());
		$this->setTitle($this->lng->txt(""));

		$this->addColumn($this->lng->txt("cont_filter_record"));
		$this->addColumn($this->lng->txt("cont_filter_field"));
		//$this->addColumn($this->lng->txt("actions"));

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.cont_filter_row.html", "Services/Container/Filter");

		//$this->addMultiCommand("", $this->lng->txt(""));
		//$this->addCommandButton("", $this->lng->txt(""));
	}

	/**
	 * Get items
	 *
	 * @return array[]
	 */
	protected function getItems()
	{
		$service = $this->container_filter_service;

		$items = array_map(function($i) use ($service) {
			/** @var ilContainerFilterField $i */
			return array(
				"record_set_id" => $i->getRecordSetId(),
				"record_title" => $service->util()->getContainerRecordTitle($i->getRecordSetId()),
				"field_title" => $service->util()->getContainerFieldTitle($i->getRecordSetId(), $i->getFieldId())
			);
		}, $service->data()->getFilterSetForRefId($this->ref_id)->getFields());
		return $items;
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		$tpl = $this->tpl;

		$tpl->setVariable("RECORD_TITLE", $a_set["record_title"]);
		$tpl->setVariable("FIELD_TITLE", $a_set["field_title"]);
	}
}