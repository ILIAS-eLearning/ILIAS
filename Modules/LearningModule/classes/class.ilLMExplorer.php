<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/*
* Explorer View for Learning Modules
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesIliasLearningModule
*/

require_once("./classes/class.ilExplorer.php");

class ilLMExplorer extends ilExplorer
{

	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $root_id;
	var $lm_obj;
	var $output;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function ilLMExplorer($a_target,&$a_lm_obj)
	{
		parent::ilExplorer($a_target);
		$this->tree = new ilTree($a_lm_obj->getId());
		$this->tree->setTableNames('lm_tree','lm_data');
		$this->tree->setTreeTablePK("lm_id");
		$this->root_id = $this->tree->readRootId();
		$this->lm_obj =& $a_lm_obj;
		$this->order_column = "";
		$this->setSessionExpandVariable("lmexpand");
		$this->checkPermissions(false);
		$this->setPostSort(false);
		$this->textwidth = 200;
	}

	/**
	* overwritten method from base class
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader(&$tpl, $a_obj_id,$a_option)
	{
		global $lng, $ilias;
		
		$tpl->setCurrentBlock("icon");
		$tpl->setVariable("ICON_IMAGE" , ilUtil::getImagePath("icon_lm_s.gif",false, "output", $this->offlineMode()));
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt("obj_".$this->lm_obj->getType()));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("link");
		$tpl->setVariable("TITLE", ilUtil::shortenText($this->lm_obj->getTitle(), $this->textwidth, true));
		$tpl->setVariable("LINK_TARGET", $this->buildLinkTarget("",""));
		$tpl->setVariable("TARGET", " target=\"".$this->frame_target."\"");
		$tpl->parseCurrentBlock();
		
		$tpl->touchBlock("element");
	}

	/**
	* check if links for certain object type are activated
	*
	* @param	string		$a_type			object type
	*
	* @return	boolean		true if linking is activated
	*/
	function isClickable($a_type, $a_obj_id = 0)
	{
		global $ilUser;
		// in this standard implementation
		// only the type determines, wether an object should be clickable or not
		// but this method can be overwritten and make use of the ref id
		// (this happens e.g. in class ilRepositoryExplorerGUI)
		if ($this->is_clickable[$a_type] == "n")
		{
			return false;
		}

		// check public access
		include_once 'payment/classes/class.ilPaymentObject.php';
		if (($ilUser->getId() == ANONYMOUS_USER_ID || 
			ilPaymentObject::_requiresPurchaseToAccess((int)$this->lm_obj->getRefId())) &&
		    !ilLMObject::_isPagePublic($a_obj_id, true))
		{
			return false;
		}
	
		return true;
	}
} // END class ilLMExplorer
?>
