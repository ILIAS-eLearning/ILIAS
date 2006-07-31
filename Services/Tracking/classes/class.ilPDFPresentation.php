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
* Class for generation of PDF documents
* 
*
*
* @ilCtrl_Calls ilPDFPresentation:
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


class ilPDFPresentation extends ilLearningProgressBaseGUI
{
	var $type = '';


	function ilPDFPresentation($a_mode,$a_ref_id,$a_user_id = 0)
	{
		global $lng,$ilCtrl;

		parent::ilLearningProgressBaseGUI($a_mode,$a_ref_id,$a_user_id);

		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;

		$this->__init();
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
				$cmd = $this->ctrl->getCmd();
				$this->$cmd();

		}
		return true;
	}

	function setType($a_type)
	{
		$this->type = $a_type;
	}
	function getType()
	{
		return $this->type;
	}


	// PRIVATE
	function __init()
	{
		global $ilUser;

		include_once './classes/class.ilXmlWriter.php';
		$this->xmlWriter = new ilXmlWriter();
		

		include_once './Services/Tracking/classes/class.ilLPFilter.php';
		$this->filter = new ilLPFilter($ilUser->getId());


	}

	function createList()
	{
		switch($this->getType())
		{
			case LP_ACTIVE_PROGRESS:
				return $this->__createPersonalProgressList();

			default:
				sendInfo('trac_pdf_error'. ' '.$this->getMode(),true);
		}

		$this->ctrl->returnToParent($this);
	}

	function __createPersonalProgressList()
	{
		// Load and fill xml template
		$this->xml_tpl = new ilTemplate('tpl.lp_pdf_list.xml',true,true,'Services/Tracking');

		$this->__addMain();
		$this->__addFilter();

		// Finally convert to fop
		$this->__convert();
	}

	function __addFilter()
	{
		global $ilUser,$ilObjDataCache;

		$filter = new ilLPFilter($ilUser->getId());
		
		$this->xml_tpl->addBlockFile('FILTER','filter','tpl.lp_pdf_filter.xml','Services/Tracking');
		$this->xml_tpl->setVariable("LEARNING_PROGRESS_OF",$this->lng->txt('learning_progress'));

		$name = ilObjUser::_lookupName($ilUser->getId());
		$this->xml_tpl->setVariable("USER_FULLNAME",$name['lastname'].', '.$name['firstname']);
		$this->xml_tpl->setVariable("DATE",ilFormat::formatUnixTime(time()));
		$this->xml_tpl->setVariable("TXT_FILTER",$this->lng->txt('trac_lp_filter'));
		$this->xml_tpl->setVariable("TXT_TYPE",$this->lng->txt('obj_types'));
		$this->xml_tpl->setVariable("TYPE",$this->lng->txt('objs_'.$filter->getFilterType()));
		$this->xml_tpl->setVariable("TXT_AREA",$this->lng->txt('trac_filter_area'));
		$this->xml_tpl->setVariable("FILTER_LANG",$ilUser->getLanguage());
		if($filter->getRootNode() == ROOT_FOLDER_ID)
		{
			$this->xml_tpl->setVariable("AREA",$this->lng->txt('trac_filter_repository'));
		}
		else
		{
			$text = $this->lng->txt('trac_below')." '";
			$text .= $ilObjDataCache->lookupTitle($ilObjDataCache->lookupObjId($filter->getRootNode()));
			$text .= "'";
			$this->xml_tpl->setVariable("AREA",$text);
		}
		$this->xml_tpl->parseCurrentBlock();

		#echo htmlentities($this->xml_tpl->get());
	}

	function __addMain()
	{
		global $ilUser;

		$this->xml_tpl->setVariable("LANG",$ilUser->getLanguage());
		$this->xml_tpl->setVariable("PDF_TITLE",$this->lng->txt('learning_progress'));
		$this->xml_tpl->setVariable("PDF_DESCRIPTION",$this->lng->txt('learning_progress'));
		$this->xml_tpl->setVariable("PAGE_TITLE",$this->lng->txt('learning_progress'));
		$this->xml_tpl->setVariable("PAGE_DESCRIPTION",$this->lng->txt('learning_progress'));
		
		return true;
	}


	function __convert()
	{
		include_once 'Services/Transformation/classes/class.ilContentObject2FO.php';

		$co2fo = new ilContentObject2FO();
		$co2fo->setXMLString($xml = $this->xml_tpl->get());

		if(!$co2fo->transform())
		{
			sendInfo($this->lng->txt('trac_error_pdf',true));
			$this->ctrl->returnToParent($this);
		}

		include_once 'Services/Transformation/classes/class.ilFO2PDF.php';

		$fo2pdf = new ilFO2PDF();
		$fo2pdf->setFOString($co2fo->getFOString());

		$pdf_base64 = $fo2pdf->send();

		if(is_null($pdf_base64))
		{
			sendInfo($this->lng->txt('trac_error_pdf',true));
			$this->ctrl->returnToParent($this);
		}
		ilUtil::deliverData($pdf_base64,'learning_progress.pdf','application/pdf');
	}
		
}	
?>