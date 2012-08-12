<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesScormAicc
 */
class ilSCORMTrackingItemsScoTableGUI extends ilTable2GUI
{
    private $obj_id = 0;
	private $user_id = 0;
	private $sco = null;

	/**
	 * Constructor
	 */
	public function __construct($a_obj_id,$a_parent_obj,$a_parent_cmd)
	{
		$this->obj_id = $a_obj_id;

		$this->setId('sco_tr_sco_'.$this->obj_id);
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
	 * Set sco id
	 * @param int $a_sco_id 
	 */
	public function setScoId($a_sco_id)
	{
		include_once './Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php';
		$this->sco = new ilSCORMItem($a_sco_id);
	}

	/**
	 * Get sco
	 * @return ilSCORMItem $sco
	 */
	public function getSco()
	{
		return $this->sco;
	}

	/**
	 * Parse table content
	 */
	public function parse()
	{
		$this->initTable();

		$sco_data = $this->getParentObject()->object->getTrackingDataAggSco($this->getSco()->getId());

		$data = array();
		foreach($sco_data as $row)
		{
			$tmp = array();
			$tmp['user_id'] = $row['user_id'];
			$tmp['score'] = $row['score'];
			$tmp['time'] = $row['time'];
			$tmp['status'] = $row['status'];
			$tmp['name'] = ilObjUser::_lookupFullname($row['user_id']);

			$data[] = $tmp;
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

		$ilCtrl->setParameter($this->getParentObject(),'user_id',$a_set['user_id']);
		$ilCtrl->setParameter($this->getParentObject(),'obj_id',$this->getSco()->getId());
		$this->tpl->setVariable('LINK_USER', $ilCtrl->getLinkTarget($this->getParentObject(),'showTrackingItemPerUser'));
		$this->tpl->setVariable('VAL_USERNAME', $a_set['name']);

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
		$this->setRowTemplate('tpl.scorm_track_item_sco.html', 'Modules/ScormAicc');
		$this->setTitle($this->getSco()->getTitle());

		$this->addColumn($this->lng->txt('name'), 'name','35%');
		$this->addColumn($this->lng->txt('cont_status'), 'status', '25%');
		$this->addColumn($this->lng->txt('cont_time'), 'time', '20%');
		$this->addColumn($this->lng->txt('cont_score'), 'score', '20%');
	}
}
?>
