<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * List participant / booking pool  assignment.
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingAssignParticipantsTableGUI extends ilTable2GUI
{
	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var int
	 */
	protected $ref_id;

	/**
	 * @var int
	 */
	protected $pool_id;

	/**
	 * @var int
	 */
	protected $bp_object_id;

	/**
	 * @var ilObjBookingPool
	 */
	protected $bp_object;

	/**
	 * @var int
	 */
	protected $current_bookings; // [int]

	/**
	 * @var array
	 */
	protected $filter; // [array]

	/**
	 * @var array
	 */
	protected $objects; // array

	/**
	 * Constructor
	 * @param	ilBookingObjectGUI 	$a_parent_obj
	 * @param	string	$a_parent_cmd
	 * @param	int		$a_ref_id
	 * @param	int		$a_pool_id
	 * @param	int		$a_booking_obj_id //booking object to assign users.
	 */
	function __construct(ilBookingObjectGUI $a_parent_obj, $a_parent_cmd, $a_ref_id, $a_pool_id, $a_booking_obj_id)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->user = $DIC->user();
		$this->access = $DIC->access();
		$this->ref_id = $a_ref_id;
		$this->bp_object_id = $a_booking_obj_id;
		$this->pool_id = $a_pool_id;
		$this->bp_object = new ilBookingObject($a_booking_obj_id);

		$this->setId("bkaprt".$a_ref_id);

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($this->lng->txt("book_assign_participant").": ".$this->bp_object->getTitle());

		$this->addColumn("", "", 1);
		$this->addColumn($this->lng->txt("name"), "name");
		$this->addColumn($this->lng->txt("book_bobj"));
		$this->addColumn($this->lng->txt("action"));

		$this->setDefaultOrderField("name");
		$this->setDefaultOrderDirection("asc");

		$this->setEnableHeader(true);
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.booking_assign_participant_row.html", "Modules/BookingManager");

		$this->setSelectAllCheckbox('mass');
		$this->addHiddenInput('object_id', $a_booking_obj_id);
		$this->addMultiCommand("bookMultipleParticipants",$this->lng->txt("assign"));
		$this->getItems();

		ilUtil::sendInfo(
			sprintf(
				$this->lng->txt("book_objects_available"),
				ilBookingReservation::numAvailableFromObjectNoSchedule($a_booking_obj_id)
			)
		);

	}

	/**
	 * Gather data and build rows
	 */
	function getItems()
	{
		include_once "Modules/BookingManager/classes/class.ilBookingParticipant.php";
		$data = ilBookingParticipant::getAssignableParticipants($this->bp_object_id);
		$this->setMaxCount(sizeof($data));
		$this->setData($data);
	}

	/**
	 * Fill table row
	 * @param	array	$a_set
	 */
	protected function fillRow($a_set)
	{
		$this->tpl->setVariable("MULTI_ID", $a_set['user_id']);
		$this->tpl->setVariable("TXT_NAME", $a_set['name']);
		$this->tpl->setCurrentBlock('object_titles');
		foreach($a_set['object_title'] as $obj_title)
		{
			$this->tpl->setVariable("TXT_OBJECT", $obj_title);
			$this->tpl->parseCurrentBlock();
		}

		$this->ctrl->setParameter($this->parent_obj, 'bkusr', $a_set['user_id']);
		$this->ctrl->setParameter($this->parent_obj, 'object_id', $this->bp_object_id);

		$this->tpl->setVariable("TXT_ACTION", $this->lng->txt("book_assign"));
		$this->tpl->setVariable("URL_ACTION", $this->ctrl->getLinkTarget($this->parent_obj, 'book'));

		$this->ctrl->setParameter($this->parent_obj, 'bkusr', '');
		$this->ctrl->setParameter($this->parent_obj, 'object_id', '');

	}
}

?>