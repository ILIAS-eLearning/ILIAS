<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesScormAicc
 */
class ilSCORMTrackingItemPerUserTableGUI extends ilTable2GUI
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

		$this->setId('sco_tr_usr_'.$this->obj_id);
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
	 * Set sco id
	 * @param int $a_sco_id
	 */
	public function setScoId($a_sco_id)
	{
		include_once("./Modules/ScormAicc/classes/SCORM/class.ilSCORMItem.php");
		$this->sco = new ilSCORMItem($a_sco_id);
	}

	/**
	 * Get SCORM item
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

		$sco_data = $this->getParentObject()->object->getTrackingDataPerUser(
			$this->getSco()->getId(),
			$this->getUserId()
		);

		$data = array();
		foreach($sco_data as $row)
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

		$this->tpl->setVariable('VAR', $a_set['lvalue']);
		$this->tpl->setVariable('VAL', $a_set['rvalue']);
	}

	/**
	 * Init table
	 */
	protected function initTable()
	{
		global $ilCtrl;


		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
		$this->setRowTemplate('tpl.scorm_track_item_per_user.html', 'Modules/ScormAicc');
		$this->setTitle(
			$this->getSco()->getTitle().' - '.
			ilObjUser::_lookupFullname($this->getUserId())
		);

		$this->addColumn($this->lng->txt('cont_lvalue'), 'lvalue', '50%');
		$this->addColumn($this->lng->txt('cont_rvalue'), 'rvalue', '50%');
	}
}
?>
