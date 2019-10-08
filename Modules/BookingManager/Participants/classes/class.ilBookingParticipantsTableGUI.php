<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * List booking participants
 *
 * @author Jesús López <lopez@leifos.com>
 * @version $Id$
 *
 * @ingroup ModulesBookingManager
 */
class ilBookingParticipantsTableGUI extends ilTable2GUI
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
	protected $ref_id; // [int]

	/**
	 * @var int
	 */
	protected $pool_id;	// [int]

	/**
	 * @var
	 */
	protected $filter; // [array]

	/**
	 * @var
	 */
	protected $objects; // array
	
	/**
	 * Constructor
	 * @param	ilBookingParticipantGUI 	$a_parent_obj
	 * @param	string	$a_parent_cmd
	 * @param	int		$a_ref_id
	 * @param	int		$a_pool_id
	 */
	function __construct(ilBookingParticipantGUI $a_parent_obj, $a_parent_cmd, $a_ref_id, $a_pool_id)
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		$this->user = $DIC->user();
		$this->access = $DIC->access();
		$this->ref_id = $a_ref_id;
		$this->pool_id = $a_pool_id;

		$this->setId("bkprt".$a_ref_id);

		parent::__construct($a_parent_obj, $a_parent_cmd);

		$this->setTitle($this->lng->txt("participants"));

		$this->addColumn($this->lng->txt("name"), "name");
		$this->addColumn($this->lng->txt("book_bobj"));
		$this->addColumn($this->lng->txt("action"));

		$this->setDefaultOrderField("name");
		$this->setDefaultOrderDirection("asc");

		$this->setEnableHeader(true);
		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));
		$this->setRowTemplate("tpl.booking_participant_row.html", "Modules/BookingManager");
		$this->setResetCommand("resetParticipantsFilter");
		$this->setFilterCommand("applyParticipantsFilter");
		$this->setDisableFilterHiding(true);

		$this->initFilter();

		$this->getItems($this->getCurrentFilter());
	}

	function initFilter()
	{
		//object
		$this->objects = array();
		foreach(ilBookingObject::getList($this->pool_id) as $item)
		{
			$this->objects[$item["booking_object_id"]] = $item["title"];
		}
		$item = $this->addFilterItemByMetaType("object", ilTable2GUI::FILTER_SELECT);
		$item->setOptions(array(""=>$this->lng->txt('book_all'))+$this->objects);
		$this->filter["object"] = $item->getValue();

		$title = $this->addFilterItemByMetaType(
			"title",
			ilTable2GUI::FILTER_TEXT,
			false,
			$this->lng->txt("object")." ".$this->lng->txt("title")."/".$this->lng->txt("description")
		);
		$this->filter["title"] = $title->getValue();

		//user
		$options = array(""=>$this->lng->txt('book_all'))+
			ilBookingParticipant::getUserFilter($this->pool_id);
		$item = $this->addFilterItemByMetaType("user", ilTable2GUI::FILTER_SELECT);
		$item->setOptions($options);
		$this->filter["user_id"] = $item->getValue();
	}

	/**
	 * Get current filter settings
	 * @return	array
	 */
	function getCurrentFilter()
	{
		$filter = array();
		if($this->filter["object"])
		{
			$filter["object"] = $this->filter["object"];
		}
		if($this->filter["title"])
		{
			$filter["title"] = $this->filter["title"];
		}
		if($this->filter["user_id"])
		{
			$filter["user_id"] = $this->filter["user_id"];
		}

		return $filter;
	}

	/**
	 * Gather data and build rows
	 * @param array $filter
	 */
	function getItems(array $filter)
	{
		if($filter["object"]) {
			$data = ilBookingParticipant::getList($this->pool_id, $filter, $filter["object"]);
		} else {
			$data = ilBookingParticipant::getList($this->pool_id, $filter);
		}

		$this->setMaxCount(sizeof($data));
		$this->setData($data);
	}

	/**
	 * Fill table row
	 * @param	array	$a_set
	 */
	protected function fillRow($a_set)
	{
		$ctrl = $this->ctrl;
		$lng = $this->lng;

		$this->tpl->setVariable("TXT_NAME", $a_set['name']);
		$this->tpl->setCurrentBlock('object_titles');
		foreach($a_set['object_title'] as $obj_title)
		{
			$this->tpl->setVariable("TXT_OBJECT", $obj_title);
			$this->tpl->parseCurrentBlock();
		}

		// determin actions form data
		// action assign only if user did not booked all objects.
		$actions = [];
		if($a_set['obj_count'] < ilBookingObject::getNumberOfObjectsForPool($this->pool_id))
		{
			$ctrl->setParameterByClass('ilbookingparticipantgui', 'bkusr', $a_set['user_id']);
			$actions[] = array(
				'text' => $lng->txt("book_assign_object"),
				'url' => $ctrl->getLinkTargetByClass("ilbookingparticipantgui", 'assignObjects')
			);
			$ctrl->setParameterByClass('ilbookingparticipantgui', 'bkusr', '');
		}

		$bp = new ilObjBookingPool($this->pool_id, false);
		if($bp->getScheduleType() == ilObjBookingPool::TYPE_NO_SCHEDULE && $a_set['obj_count'] == 1)
		{
			$ctrl->setParameterByClass('ilbookingobjectgui', 'bkusr', $a_set['user_id']);
			$ctrl->setParameterByClass('ilbookingobjectgui', 'object_id', $a_set['object_ids'][0]);
			$ctrl->setParameterByClass('ilbookingobjectgui', 'part_view',ilBookingParticipantGUI::PARTICIPANT_VIEW);

			$actions[] = array(
				'text' => $lng->txt("book_deassign"),
				'url' => $ctrl->getLinkTargetByClass("ilbookingobjectgui", 'rsvConfirmCancelUser')
			);

			$ctrl->setParameterByClass('ilbookingparticipantgui', 'bkusr', '');
			$ctrl->setParameterByClass('ilbookingparticipantgui', 'object_id', '');
			$ctrl->setParameterByClass('ilbookingobjectgui', 'part_view', '');
		}
		else if($bp->getScheduleType() == ilObjBookingPool::TYPE_FIX_SCHEDULE || $a_set['obj_count'] > 1)
		{
			$ctrl->setParameterByClass('ilobjbookingpoolgui', 'user_id', $a_set['user_id']);
			$actions[] = array(
				'text' => $lng->txt("book_deassign"),
				'url' => $ctrl->getLinkTargetByClass("ilobjbookingpoolgui", 'log')
			);
			$ctrl->setParameterByClass('ilobjbookingpoolgui', 'user_id', '');
		}

		$this->tpl->setCurrentBlock('actions');
		foreach($actions as $action)
		{
			$this->tpl->setVariable("TXT_ACTION", $action['text']);
			$this->tpl->setVariable("URL_ACTION", $action['url']);
			$this->tpl->parseCurrentBlock();
		}
	}
}

?>