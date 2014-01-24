<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesScormAicc
 */
class ilSCORMTrackingItemsPerUserTableGUI extends ilTable2GUI
{
    private $obj_id = 0;
	private $user_id = 0;

	/**
	 * Constructor
	 */
	public function __construct($a_obj_id,$a_parent_obj,$a_parent_cmd)
	{
		$this->obj_id = $a_obj_id;

		$this->setId('sco_trs_usr_'.$this->obj_id);
		parent::__construct($a_parent_obj, $a_parent_cmd);
	}

	/**
	 * Get Obj id
	 * @return int
	 */
	public function getObjId()
	{
		return $this->obj_id;
	}

	/**
	 * Set current user id
	 * @param int $a_usr_id 
	 */
	public function setUserId($a_usr_id)
	{
		$this->user_id = $a_usr_id;
	}

	/**
	 * Get user id
	 * @return int
	 */
	public function getUserId()
	{
		return $this->user_id;
	}

	/**
	 * Parse table content
	 */
	public function parse()
	{
		$this->initTable();

		$user_data = $this->getParentObject()->object->getTrackingDataAgg($this->getUserId());
		$data = array();
		foreach($user_data as $row)
		{
			$data[] = $row;
		}
		$this->setData($data);
	}


	/**
	 * Fill row template
	 * @param array $a_set
	 */
	protected function  fillRow($a_set)
	{
		global $ilCtrl;

		$this->tpl->setVariable('VAL_TITLE', $a_set['title']);

		$ilCtrl->setParameter($this->getParentObject(),'user_id',$this->getUserId());
		$ilCtrl->setParameter($this->getParentObject(),'obj_id',$a_set['sco_id']);
		$this->tpl->setVariable('LINK_SCO', $ilCtrl->getLinkTarget($this->getParentObject(),'showTrackingItemPerUser'));

		$this->tpl->setVariable('VAL_STATUS', $a_set['status']);
		$this->tpl->setVariable('VAL_TIME', $a_set['time']);
		$this->tpl->setVariable('VAL_SCORE', $a_set['score']);
	}

	/**
	 * Init table
	 */
	protected function initTable()
	{
		global $ilCtrl;


		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
		$this->setRowTemplate('tpl.scorm_track_item.html', 'Modules/ScormAicc');
		$this->setTitle(ilObjUser::_lookupFullname($this->getUserId()));

		$this->addColumn($this->lng->txt('title'), 'title','35%');
		$this->addColumn($this->lng->txt('cont_status'), 'status', '25%');
		$this->addColumn($this->lng->txt('cont_time'), 'time', '20%');
		$this->addColumn($this->lng->txt('cont_score'), 'score', '20%');
	}
}
?>
