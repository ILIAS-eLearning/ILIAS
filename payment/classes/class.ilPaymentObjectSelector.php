<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/

/*
* Repository Explorer
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$
*
* @package core
*/

require_once("classes/class.ilExplorer.php");
require_once './payment/classes/class.ilPaymentObject.php';


class ilPaymentObjectSelector extends ilExplorer
{

	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $root_id;
	var $output;
	var $ctrl;

	var $selectable_type;
	var $ref_id;

	var $classname;
	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilPaymentObjectSelector($a_target, $a_classname)
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
				break;
			default:
				return false;
		}


		if ($this->classname == 'ilpaymentstatisticgui')
		{
			if (!ilPaymentObject::_isPurchasable($a_ref_id, $ilUser->getId()))
			{
				return true;
			}
		}
		else if ($this->classname == 'ilobjpaymentsettingsgui')
		{
			if (!ilPaymentObject::_isPurchasable($a_ref_id))
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
		global $rbacsystem;

		return true;

		if ($a_ref_id == 0)
		{
			return true;
		}

		if ($this->classname == 'ilpaymentstatisticgui')
		{
			if (!ilPaymentObject::_isPurchasable($a_ref_id, $ilUser->getId()))
			{
				return false;
			}
		}
		else if ($this->classname == 'ilobjpaymentsettingsgui')
		{
			if (!ilPaymentObject::_isPurchasable($a_ref_id))
			{
				return false;
			}
		}
		else
		{
			if (!ilPaymentObject::_isPurchasable($a_ref_id))
			{
				return false;
			}
		}

		if($rbacsystem->checkAccess("visible", $a_ref_id))
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	* overwritten method from base class
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader($a_obj_id,$a_option)
	{
		global $lng, $ilias;

		$tpl = new ilTemplate("tpl.tree.html", true, true);

		$tpl->setCurrentBlock("text");
		$tpl->setVariable("OBJ_TITLE", $lng->txt("repository"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("row");
		$tpl->parseCurrentBlock();

		$this->output[] = $tpl->get();
	}

} // END class ilObjectSelector
?>
