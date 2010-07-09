<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
 * name table
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com> 
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingTypesTableGUI extends ilTable2GUI
{
	/**
	 * Constructor
	 */
	function __construct($a_parent_obj, $a_parent_cmd, $a_ref_id)
	{
		global $ilCtrl, $lng, $ilAccess, $lng, $ilObjDataCache;

		$this->setId("bktp");

		parent::__construct($a_parent_obj, $a_parent_cmd);
		$this->setLimit(9999);
		
		if ($ilAccess->checkAccess('write', '', $a_ref_id))
		{
			$this->addCommandButton('addType', $this->lng->txt('book_add_type'));
		}

		$this->addColumn($this->lng->txt("title"), "title");

		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.booking_type_row.html", "Modules/BookingManager");
		// $this->initFilter($a_parent_obj->getObjId());

		$this->getItems($ilObjDataCache->lookupObjId($a_ref_id), $this->getCurrentFilter());
	}

	/**
	* Init filter
	*/
	function initFilter($a_obj_id)
	{
		global $lng;

		$item = $this->addFilterItemByMetaType("country", ilTable2GUI::FILTER_TEXT, true);
		$this->filter["country"] = $item->getValue();
	}

	/**
	 *
	 *
	 */
	function getCurrentFilter()
	{

	}
	
	/**
	 * Build summary item rows for given object and filter(s
	 *
	 * @param	array	&$rows
	 * @param	int		$object_id
	 * @param	array	$filter
	 */
	function getItems($object_id, array $filter = NULL)
	{
		global $lng;

		include_once 'Modules/BookingManager/classes/class.ilBookingType.php';
		$data = ilBookingType::getList($object_id);
		
		$this->setMaxCount(sizeof($data));
		$this->setData($data);
	}

	/**
	 * Fill table row
	 */
	protected function fillRow($a_set)
	{
		global $lng;

	    $this->tpl->setVariable("TITLE_TXT", $a_set["title"]);

	}
}
?>
