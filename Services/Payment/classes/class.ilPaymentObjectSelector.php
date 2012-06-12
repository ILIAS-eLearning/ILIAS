<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/*
* Repository Explorer
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id: class.ilPaymentObjectSelector.php 20462 2009-07-07 15:33:41Z mjansen $
*
* @package core
*/

require_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");
require_once './Services/Payment/classes/class.ilPaymentObject.php';


class ilPaymentObjectSelector extends ilExplorer
{

	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	public $root_id;
	public $output;
	public $ctrl;

	public $selectable_type;
	public $ref_id;

	public $classname;

	/**
	 * @access	public
	 * @param $a_target (i.e. ilias.php?cmd=showObjectSelector&cmdClass=ilpaymentobjectgui&cmdNode=8n:8z:90&baseClass=ilShopController)
	 * @param string $a_classname i.e. ilpaymentobjectgui
	 */

	public function __construct($a_target, $a_classname)
	{
		global $tree,$ilCtrl;

		$this->ctrl = $ilCtrl;
		
		parent::ilExplorer($a_target);
		$this->tree = $tree;
		$this->root_id = $this->tree->readRootId();
		$this->order_column = "title";

		$this->setSessionExpandVariable("paya_link_expand");

		$this->addFilter("adm");
		$this->addFilter("rolf");
		$this->addFilter("chat");
		#$this->addFilter('fold');

		$this->setFilterMode(IL_FM_NEGATIVE);
		$this->setFiltered(true);

		$this->classname = $a_classname;
	}

	function buildLinkTarget($a_node_id, $a_type)
	{
		$this->ctrl->setParameterByClass($this->classname,'sell_id',$a_node_id);
		
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

	function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
	{
		return '';
	}

	function isClickable($a_type, $a_ref_id)
	{
		global $ilUser;

		switch($a_type)
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
			if (ilPaymentObject::_isPurchasable($a_ref_id, $ilUser->getId(), true))
			#if (!ilPaymentObject::_isPurchasable($a_ref_id, $ilUser->getId(), true))
			{
				return true;
			}
		}
		else if ($this->classname == 'ilobjpaymentsettingsgui')
		{
			if (ilPaymentObject::_isPurchasable($a_ref_id))
			#if (!ilPaymentObject::_isPurchasable($a_ref_id))
			{
				return true;
			}
		}
		else if($this->classname == 'ilpaymentobjectgui')
		{
			// object doesn't exist in payment_object
			if(ilPaymentObject::_isNewObject($a_ref_id))
			{
				return true;
			}
			
		}
		else
		{
			if (ilPaymentObject::_isPurchasable($a_ref_id))
			{
				return true;
			}
		}
		return false;
		
	}

	function setAlwaysClickable($a_value)
	{
		$this->always_clickable = $a_value;
	}

	function showChilds($a_ref_id)
	{

		// depricated?!
		return true;
//	global $rbacsystem;
//		if ($a_ref_id == 0)
//		{
//			return true;
//		}
//
//		if ($this->classname == 'ilpaymentstatisticgui')
//		{
//			if (!ilPaymentObject::_isPurchasable($a_ref_id, $ilUser->getId(), true))
//			{
//				return false;
//			}
//		}
//		else if ($this->classname == 'ilobjpaymentsettingsgui')
//		{
//			if (!ilPaymentObject::_isPurchasable($a_ref_id))
//			{
//				return false;
//			}
//		}
//		else
//		{
//			if (!ilPaymentObject::_isPurchasable($a_ref_id))
//			{
//				return false;
//			}
//		}
//
//		if($rbacsystem->checkAccess("visible", $a_ref_id))
//		{
//			return true;
//		}
//		else
//		{
//			return false;
//		}
	}


	/**
	* overwritten method from base class
	* @access	public
	* @param	integer a_obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader($a_obj_id,$a_option)
	{
		global $lng;

		$tpl = new ilTemplate("tpl.tree.html", true, true, "Services/UIComponent/Explorer");

		$tpl->setCurrentBlock("text");
		$tpl->setVariable("OBJ_TITLE", $lng->txt("repository"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("row");
		$tpl->parseCurrentBlock();

		$this->output[] = $tpl->get();
	}

} // END class ilObjectSelector
?>
