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
* Class ilObjUserTrackingGUI
*
* @author Stefan Meyer <smeyer@databay.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilLPListOfObjectsGUI: ilLPFilterGUI
*
* @package ilias-tracking
*
*/

include_once './Services/Tracking/classes/class.ilLearningProgressBaseGUI.php';
include_once './Services/Tracking/classes/class.ilLPStatusWrapper.php';

class ilLPListOfObjectsGUI extends ilLearningProgressBaseGUI
{
	function ilLPListOfObjectsGUI($a_mode,$a_ref_id)
	{
		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id);

		$this->__initFilterGUI();
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$this->ctrl->setReturn($this, "");

		switch($this->ctrl->getNextClass())
		{
			case 'illpfiltergui':

				$this->ctrl->forwardCommand($this->filter_gui);
				break;

			default:
				$cmd = $this->__getDefaultCommand();
				$this->$cmd();

		}
		return true;
	}

	function show()
	{
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.lp_list_objects.html','Services/Tracking');
		$this->__showFilter();
		$this->__showItems();
	}

	// Private
	function __showFilter()
	{
		$this->tpl->setVariable("FILTER",$this->filter_gui->getHTML());
	}

	function __showItems()
	{
		$this->__initFilter();

		$tpl = new ilTemplate('tpl.lp_objects.html',true,true,'Services/Tracking');

		if(!count($objs = $this->filter->getObjects()))
		{
			sendInfo($this->lng->txt('trac_filter_no_access'));
			return true;
		}
		$type = $this->filter->getFilterType();
		$tpl->setVariable("HEADER_IMG",ilUtil::getImagePath('icon_'.$type.'.gif'));
		$tpl->setVariable("HEADER_ALT",$this->lng->txt('objs_'.$type));
		$tpl->setVariable("BLOCK_HEADER_CONTENT",$this->lng->txt('objs_'.$type));

		$counter = 0;
		foreach($objs as $obj_id => $obj_data)
		{
			$tpl->touchBlock(ilUtil::switchColor($counter++,'row_type_1','row_type_2'));
			$tpl->setCurrentBlock("container_standard_row");
			$tpl->setVariable("ITEM_ID",$obj_id);

			$obj_tpl = new ilTemplate('tpl.lp_object.html',true,true,'Services/Tracking');
			$obj_tpl->setCurrentBlock("item_title");
			$obj_tpl->setVariable("TXT_TITLE",$obj_data['title']);
			$obj_tpl->parseCurrentBlock();

			if(strlen($obj_data['description']))
			{
				$obj_tpl->setCurrentBlock("item_description");
				$obj_tpl->setVariable("TXT_DESC",$obj_data['description']);
				$obj_tpl->parseCurrentBlock();
			}
			$obj_tpl->setCurrentBlock("item_command");
			$this->ctrl->setParameterByClass('illpfiltergui','hide',$obj_id);
			$obj_tpl->setVariable("HREF_COMMAND",$this->ctrl->getLinkTargetByClass('illpfiltergui','hide'));
			$obj_tpl->setVariable("TXT_COMMAND",$this->lng->txt('trac_hide'));
			$obj_tpl->parseCurrentBlock();

			$obj_tpl->setVariable("OCCURRENCES",$this->lng->txt('trac_occurrences'));
			foreach($obj_data['ref_ids'] as $ref_id)
			{
				$this->__insertPath($obj_tpl,$ref_id);
			}

			$obj_tpl->setCurrentBlock("item_property");
			$obj_tpl->setVariable("TXT_PROP",$this->lng->txt('trac_in_progress'));
			$obj_tpl->setVariable("VAL_PROP",ilLPStatusWrapper::_getCountInProgress($obj_id));
			$obj_tpl->parseCurrentBlock();

			$obj_tpl->touchBlock('newline_prop');
			$obj_tpl->setCurrentBlock("item_property");
			$obj_tpl->setVariable("TXT_PROP",$this->lng->txt('trac_completed'));
			$obj_tpl->setVariable("VAL_PROP",ilLPStatusWrapper::_getCountCompleted($obj_id));
			$obj_tpl->parseCurrentBlock();

			$obj_tpl->setCurrentBlock("item_properties");
			$obj_tpl->parseCurrentBlock();

			$tpl->setVariable("BLOCK_ROW_CONTENT",$obj_tpl->get());
			$tpl->parseCurrentBlock();
		}

		// Hide button
		$tpl->setVariable("DOWNRIGHT",ilUtil::getImagePath('arrow_downright.gif'));
		$tpl->setVariable("BTN_HIDE_SELECTED",$this->lng->txt('trac_hide'));
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormActionByClass('illpfiltergui'));

		$this->tpl->setVariable("LP_OBJECTS",$tpl->get());
	}

	function __initFilterGUI()
	{
		global $ilUser;

		include_once './Services/Tracking/classes/class.ilLPFilterGUI.php';

		$this->filter_gui = new ilLPFilterGUI($ilUser->getId());
	}

	function __initFilter()
	{
		global $ilUser;

		include_once './Services/Tracking/classes/class.ilLPFilter.php';

		$this->filter = new ilLPFilter($ilUser->getId());
	}

}
?>