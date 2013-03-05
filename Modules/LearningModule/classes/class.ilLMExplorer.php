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

require_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");

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
			
	protected $lp_cache; // [array]

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
		$tpl->setVariable("ICON_IMAGE" , ilUtil::getImagePath("icon_lm_s.png",false, "output", $this->offlineMode()));
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
		include_once 'Services/Payment/classes/class.ilPaymentObject.php';
		if (($ilUser->getId() == ANONYMOUS_USER_ID || 
			ilPaymentObject::_requiresPurchaseToAccess((int)$this->lm_obj->getRefId())) &&
		    !ilLMObject::_isPagePublic($a_obj_id, true))
		{
			return false;
		}
	
		return true;
	}
	
	protected function checkLPIcon($a_id)
	{
		global $ilUser;
						
		// do it once for all chapters
		if($this->lp_cache[$this->lm_obj->getId()] === null)
		{				
			$this->lp_cache[$this->lm_obj->getId()] = false;

			include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
			if(ilLearningProgressAccess::checkAccess($this->lm_obj->getRefId()))
			{			
				$info = null;

				include_once './Services/Tracking/classes/class.ilLPObjSettings.php';
				include_once "Services/Tracking/classes/class.ilLPStatus.php";
				$lp = new ilLPObjSettings($this->lm_obj->getId());	
				if($lp->getMode() == LP_MODE_COLLECTION_MANUAL)
				{
					include_once "Services/Tracking/classes/class.ilLPStatusCollectionManual.php";
					$info = ilLPStatusCollectionManual::_getStatusInfo($this->lm_obj->getId());						
				}
				else if($lp->getMode() == LP_MODE_COLLECTION_TLT)
				{						
					include_once "Services/Tracking/classes/class.ilLPStatusCollectionTLT.php";
					$info = ilLPStatusCollectionTLT::_getStatusInfo($this->lm_obj->getId());
				}

				// parse collection items
				if(is_array($info["items"]))
				{
					foreach($info["items"] as $item_id)
					{
						$status = LP_STATUS_NOT_ATTEMPTED_NUM;
						if(is_array($info["in_progress"][$item_id]) && 
							in_array($ilUser->getId(), $info["in_progress"][$item_id]))
						{
							$status = LP_STATUS_IN_PROGRESS_NUM;
						}
						else if(is_array($info["completed"][$item_id]) && 
							in_array($ilUser->getId(), $info["completed"][$item_id]))
						{
							$status = LP_STATUS_COMPLETED_NUM;
						}
						$this->lp_cache[$this->lm_obj->getId()][$item_id] =$status;
					}
				}						
			}		

			include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
		}		

		if(is_array($this->lp_cache[$this->lm_obj->getId()]) &&
			isset($this->lp_cache[$this->lm_obj->getId()][$a_id]))
		{
			return ilLearningProgressBaseGUI::_getImagePathForStatus($this->lp_cache[$this->lm_obj->getId()][$a_id]);					
		}				
	}
	
} // END class ilLMExplorer
?>
