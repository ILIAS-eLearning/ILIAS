<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Table/classes/class.ilTable2GUI.php';

/**
 * Table gui for copy progress
 * 
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * 
 *
 * @ingroup ServicesObject
 */
class ilObjectCopyProgressTableGUI extends ilTable2GUI
{
	protected $objects = array();

	/**
	 * Constructor
	 * @param type $a_parent_obj
	 * @param type $a_parent_cmd
	 * @param type $a_id
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_id)
	{
		$this->setId('obj_copy_progress_table_' . $a_id);
		parent::__construct($a_parent_obj, $a_parent_cmd, '');
	}

	public function setObjectInfo($a_ref_ids)
	{
		$this->objects = $a_ref_ids;
	}

	public function getObjects()
	{
		return $this->objects;
	}
	
	public function setRedirectionUrl($a_url)
	{
		$GLOBALS['tpl']->addOnLoadCode('il.CopyRedirection.setRedirectUrl("'.$a_url.'")');
	}

	/**
	 * Init Table
	 */
	public function init()
	{
		$GLOBALS['tpl']->addJavaScript('./Services/CopyWizard/js/ilCopyRedirection.js');
		$GLOBALS['tpl']->addOnLoadCode('il.CopyRedirection.checkDone()');
		$this->setExternalSorting(TRUE);
		$this->setFormAction($GLOBALS['ilCtrl']->getFormAction($this->getParentObject()));

		$this->setRowTemplate('tpl.object_copy_progress_table_row.html', 'Services/Object');

		$this->addColumn($this->lng->txt('obj_target_location'), '');
		$this->addColumn($this->lng->txt('obj_copy_progress'), '');
	}

	/**
	 * Fill row
	 * @param type $set
	 */
	public function fillRow($set)
	{
		$this->tpl->setVariable('VAL_ID', $set['ref_id']);
		$this->tpl->setVariable('OBJ_TITLE', $set['title']);

		if (strlen($set['description']))
		{
			$this->tpl->setVariable('VAL_DESC', $set['description']);
		}

		$this->tpl->setVariable('TYPE_IMG', ilUtil::getTypeIconPath($set['type'], $set['obj_id']));
		$this->tpl->setVariable('TYPE_STR', $this->lng->txt('obj_' . $set['type']));

		include_once './Services/UIComponent/ProgressBar/classes/class.ilProgressBar.php';
		$progress = ilProgressBar::getInstance();
		$progress->setType(ilProgressBar::TYPE_SUCCESS);
		$progress->setMin(0);
		$progress->setMax($set['max_steps']);
		$progress->setCurrent(0);
		$progress->setAnimated(TRUE);
		$progress->setId($set['copy_id']);

		$GLOBALS['ilCtrl']->setParameter($this->getParentObject(), 'copy_id', $set['copy_id']);
		$progress->setAsyncStatusUrl(
				$GLOBALS['ilCtrl']->getLinkTarget(
						$this->getParentObject(), 
						'updateProgress', 
						'', 
						TRUE
				)
		);

		$progress->setAsynStatusTimeout(1);
		$this->tpl->setVariable('PROGRESS_BAR', $progress->render());
	}

	/**
	 * Parse objects
	 */
	public function parse()
	{
		$counter = 0;
		$set = array();
		foreach ($this->getObjects() as $ref_id => $copy_id)
		{
			$counter++;
			$set[$counter]['ref_id'] = $ref_id;
			$set[$counter]['copy_id'] = $copy_id;
			$set[$counter]['obj_id'] = ilObject::_lookupObjId($ref_id);
			$set[$counter]['type'] = ilObject::_lookupType(ilObject::_lookupObjId($ref_id));
			$set[$counter]['title'] = ilObject::_lookupTitle(ilObject::_lookupObjId($ref_id));
			$set[$counter]['description'] = ilObject::_lookupDescription(ilObject::_lookupObjId($ref_id));
			
			include_once './Services/CopyWizard/classes/class.ilCopyWizardOptions.php';
			$copy_info = ilCopyWizardOptions::_getInstance($copy_id);
			$copy_info->read();
			$set[$counter]['max_steps'] = $copy_info->getRequiredSteps();
		}
		$this->setData($set);
	}
}
?>