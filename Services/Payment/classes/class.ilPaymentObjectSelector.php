<?php
/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/*
* ilPaymentObjectSelector
*
* @author Nadia Ahmad <nahmad@databay.de>
* @version $Id: class.ilPaymentObjectSelector.php 20462 2009-07-07 15:33:41Z mjansen $
* @ilCtrl_isCalledBy ilPaymentObjectSelector: ilPaymentStatisticGUI, ilObjPaymentSettingsGUI
* @package core
*/

include_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");
require_once './Services/Payment/classes/class.ilPaymentObject.php';


class ilPaymentObjectSelector extends ilTreeExplorerGUI
{
	/**
	 * @var $ctrl ilCtrl
	 */
	public $ctrl;

	private $classname;

	/**
	 * @param $parent_obj
	 * @param $parent_cmd
	 */
	public function __construct($parent_obj, $parent_cmd)
	{
		/**
		 *  @var $tree ilTree
		 *  @var $ctrl ilCtrl
		 */
		global $tree, $ilCtrl;
	
		$this->ctrl = $ilCtrl;

		parent::__construct("pobject_exp", $this, 'showObjectSelector', $tree);

		$this->setTypeBlackList(array('adm', 'rolf', 'chat', 'frm'));
		$this->classname = $this->ctrl->getCmdClass();
	}

	/**
	 * overwritten method from baseclass
	 * @param mixed $node
	 * @return bool
	 */
	function isNodeClickable($node)
	{
		global $ilUser;

		switch($node['type'])
		{
			case 'lm':
			case 'crs':
			case 'tst':
			case 'sahs':
			case 'file':
			case 'htlm':
			case 'exc':
			case 'glo':
				break;
			default:
				return false;
		}

		if ($this->classname == 'ilpaymentstatisticgui')
		{
			if (ilPaymentObject::_isPurchasable($node['ref_id'], $ilUser->getId(), true))
			{
				return true;
			}
		}
		else if ($this->classname == 'ilobjpaymentsettingsgui')
		{
			if (ilPaymentObject::_isPurchasable($node['ref_id']))
			{
				return true;
			}
		}
		else if($this->classname == 'ilpaymentobjectgui')
		{
			// object doesn't exist in payment_object
			if(ilPaymentObject::_isNewObject($node['ref_id']))
			{
				return true;
			}
		}
		else
		{
			if (ilPaymentObject::_isPurchasable($node['ref_id']))
			{
				return true;
			}
		}
		return false;
	}

	/**
	 *  overwritten method from baseclass
	 * Get node content
	 *
	 * @param array
	 * @return
	 */
	function getNodeContent($a_node)
	{
		global $lng;

		$title = $a_node["title"];
		if ($a_node["child"] == $this->getNodeId($this->getRootNode()))
		{
			if ($title == "ILIAS")
			{
				$title = $lng->txt("repository");
			}
		}
		return $title;
	}

	/**
	 *  overwritten method from baseclass
	 * @param mixed $node
	 * @return string
	 */
	function getNodeHref($node)
	{
		$this->ctrl->setParameterByClass($this->classname,'sell_id',$node['ref_id']);

		if ($this->classname == 'ilpaymentstatisticgui')
		{
			return $this->ctrl->getLinkTargetByClass($this->classname,'searchUser');
		}
		if ($this->classname == 'ilobjpaymentsettingsgui')
		{
			return $this->ctrl->getLinkTargetByClass($this->classname,'searchUserSP');
		}
		else
		{
			return $this->ctrl->getLinkTargetByClass($this->classname,'showSelectedObject');
		}
	}
	
} // END class ilObjectSelector
