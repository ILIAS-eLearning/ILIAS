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

/**
* GUI class for learning progress filter functionality
* Used for object and learning progress presentation
*
*
* @ilCtrl_Calls ilLPFilterGUI:
*
* 
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @package ilias-tracking
*
*/
include_once 'Services/Tracking/classes/class.ilLPFilter.php';


class ilLPFilterGUI
{
	var $usr_id = null;
	var $tpl = null;
	var $lng = null;
	var $ctrl = null;

	function ilLPFilterGUI($a_usr_id)
	{
		global $lng,$ilCtrl,$tpl;

		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		$this->tpl =& $tpl;
		$this->usr_id = $a_usr_id;
		$this->__initFilter();
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$this->ctrl->setReturn($this, "");
		switch($this->ctrl->getNextClass())
		{
			default:
				$cmd = $this->ctrl->getCmd() ? $this->ctrl->getCmd() : 'show';
				$this->$cmd();

		}
		return true;
	}

	
	function getUserId()
	{
		return $this->usr_id;
	}


	function getHTML()
	{
		global $ilObjDataCache;

		$tpl = new ilTemplate('tpl.lp_filter.html',true,true,'Services/Tracking');

		$tpl->setVariable("FILTER_ACTION",$this->ctrl->getFormAction($this));
		$tpl->setVariable("TBL_TITLE",$this->lng->txt('trac_lp_filter'));
		$tpl->setVariable("TXT_AREA",$this->lng->txt('trac_filter_area'));

		// Area
		if($this->filter->getRootNode() == ROOT_FOLDER_ID)
		{
			$tpl->setVariable("FILTER_AREA",$this->lng->txt('trac_filter_repository'));
		}
		else
		{
			$text = $this->lng->txt('trac_below')." '";
			$text .= $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($this->filter->getRootNode()));
			$text .= "'";
			$tpl->setVariable("FILTER_AREA",$text);
		}

		$tpl->setVariable("TXT_QUERY",$this->lng->txt('trac_query'));
		$tpl->setVariable("QUERY",ilUtil::prepareFormOutput($this->filter->getQueryString()));

		$tpl->setVariable("UPDATE_AREA",$this->lng->txt('change'));
		$tpl->setVariable("TYPES",$this->lng->txt('obj_types'));
		$tpl->setVariable("TYPE_SELECTOR",ilUtil::formSelect($this->filter->getFilterType(),
															 'type',
															 $this->__getPossibleTypes(),
															 false,
															 true));
		$tpl->setVariable("TXT_HIDDEN",$this->lng->txt('trac_filter_hidden'));

		if(count($hidden = $this->__prepareHidden()))
		{
			$tpl->setVariable("HIDDEN_SELECTOR",ilUtil::formSelect(0,'hide',$hidden,false,true));
			$tpl->setCurrentBlock("editable");
			$tpl->setVariable("BTN_SHOW",$this->lng->txt('trac_show_hidden'));
			$tpl->parseCurrentBlock();
		}
		else
		{
			$tpl->setVariable("HIDDEN_SELECTOR",$this->lng->txt('trac_filter_none'));
		}
		$tpl->setVariable("HREF_UPDATE_AREA",$this->ctrl->getLinkTargetByClass('illpfiltergui','selector'));
		$tpl->setVariable("DOWNRIGHT",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->setVariable("BTN_REFRESH",$this->lng->txt('trac_refresh'));

		return $tpl->get();
	}

	function hideSelected()
	{
		if(!count($_POST['item_id']))
		{
			sendInfo($this->lng->txt('trac_select_one'),true);
			$this->ctrl->returnToParent($this);
		}
		foreach($_POST['item_id'] as $item_id)
		{
			$this->filter->addHidden((int) $item_id);
		}
		$this->filter->update();
		sendInfo($this->lng->txt('trac_added_no_shown_list'),true);
		$this->ctrl->returnToParent($this);
	}			


	function hide()
	{
		$this->filter->addHidden((int) $_GET['hide']);
		$this->filter->update();
		sendInfo($this->lng->txt('trac_added_no_shown_list'),true);
		$this->ctrl->returnToParent($this);
	}

	function updateHidden()
	{
		if(!$_POST['hide'])
		{
			sendInfo($this->lng->txt('trac_select_one'),true);
			$this->ctrl->returnToParent($this);
		}
		$this->filter->removeHidden((int) $_POST['hide']);
		$this->filter->update();
		sendInfo($this->lng->txt('trac_modifications_saved'),true);
		$this->ctrl->returnToParent($this);

	}
		
	function refresh()
	{
		$this->filter->setFilterType($_POST['type']);
		$this->filter->setQueryString(ilUtil::stripSlashes($_POST['query']));
		$this->filter->update();
		$this->ctrl->returnToParent($this);

		return true;
	}

	function selector()
	{
		global $tree;

		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.trac_root_selector.html','Services/Tracking');

		include_once 'Services/Search/classes/class.ilSearchRootSelector.php';

		sendInfo($this->lng->txt('search_area_info'));

		$exp = new ilSearchRootSelector($this->ctrl->getLinkTargetByClass('illpfiltergui','selector'));
		$exp->setExpand($_GET["search_root_expand"] ? $_GET["search_root_expand"] : $tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTargetByClass('illpfiltergui','selector'));
		$exp->setTargetClass('illpfiltergui');
		$exp->setCmd('selectRoot');

		// build html-output
		$exp->setOutput(0);

		$this->tpl->setVariable("EXPLORER",$exp->getOutput());

		
	}

	function selectRoot()
	{
		$this->filter->setRootNode((int) $_GET['root_id']);
		$this->filter->update();
		sendInfo($this->lng->txt('trac_modifications_saved'),true);
		$this->ctrl->returnToParent($this);
	}

	// Private
	function __initFilter()
	{
		global $ilUser;

		$this->filter = new ilLPFilter($ilUser->getId());
		return true;
	}

	function __getPossibleTypes()
	{
		return array('lm' => $this->lng->txt('objs_lm'),
					 'crs' => $this->lng->txt('objs_crs'),
					 'tst' => $this->lng->txt('objs_tst'),
					 'grp' => $this->lng->txt('objs_grp'),
					 'exc' => $this->lng->txt('objs_exc'));
					 
	}

	function __prepareHidden()
	{
		$types = $this->filter->prepareType();


		global $ilObjDataCache;

		foreach($this->filter->getHidden() as $obj_id)
		{
			if(in_array($ilObjDataCache->lookupType($obj_id),$types))
			{
				$hidden[$obj_id] = $ilObjDataCache->lookupTitle($obj_id);
			}
		}
		return $hidden ? $hidden : array();
	}
}	
?>