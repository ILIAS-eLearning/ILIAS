<?php

/**
 * @ilCtrl_IsCalledBy ilOrguSelectInputGUI: ilFormPropertyDispatchGUI
 */

class ilOrguSelectInputGUI extends ilExplorerSelectInputGUI
{

	public function __construct($a_title, $a_postvar, $a_multi)
	{
		global $DIC;
		$id = "ousel_".md5($a_postvar);
		$orgu_explorer = new ilOrgUnitExplorerGUI($id,
			[
			],
			$this->getExplHandleCmd(),
			$DIC['tree']
		);
		$orgu_explorer->setAjax(false);
		$orgu_explorer->setSelectMode($a_postvar, $a_multi);
		return parent::__construct($a_title,$a_postvar,$orgu_explorer,$a_multi);
		$this->setType("orgu_select");
	}

	function getTitleForNodeId($a_id)
	{
		return ilObject::_lookupTitle(ilObject::_lookupObjId($a_id));
	}
}