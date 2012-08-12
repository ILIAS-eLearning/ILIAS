<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Description of class
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesScormAicc
 */
class ilSCORMTrackingItemsPerScoTableGUI extends ilTable2GUI
{
    private $obj_id = 0;

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
	 * Parse table content
	 */
	public function parse()
	{
		$this->initTable();

		$scos = $this->getParentObject()->object->getTrackedItems();

		$data = array();
		foreach($scos as $row)
		{
			$tmp = array();
			$tmp['title'] = $row->getTitle();
			$tmp['id'] = $row->getId();

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

		$this->tpl->setVariable('TXT_ITEM_TITLE', $a_set['title']);
		$ilCtrl->setParameter($this->getParentObject(),'obj_id',$a_set['id']);
		$this->tpl->setVariable('LINK_ITEM', $ilCtrl->getLinkTarget($this->getParentObject(),'showTrackingItemSco'));
	}

	/**
	 * Init table
	 */
	protected function initTable()
	{
		global $ilCtrl;


		$this->setFormAction($ilCtrl->getFormAction($this->getParentObject()));
		$this->setRowTemplate('tpl.scorm_track_items_sco.html', 'Modules/ScormAicc');
		$this->setTitle($this->lng->txt('cont_tracking_items'));

		$this->addColumn($this->lng->txt('title'), 'title','100%');
	}
}
?>
