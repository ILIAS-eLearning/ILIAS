<?php
/*
        +-----------------------------------------------------------------------------+
        | ILIAS open source                                                           |
        +-----------------------------------------------------------------------------+
        | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once('./Services/Table/classes/class.ilTable2GUI.php');

/**
*
* @author Helmut Schottmüller <ilias@aurealis.de>
* @version $Id$
*
* @ingroup ModulesSurvey
*/

class ilSurveyCodesTableGUI extends ilTable2GUI
{
	protected $counter;
	protected $confirmdelete;
	
	/**
	 * Constructor
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $confirmdelete = false)
	{
		parent::__construct($a_parent_obj, $a_parent_cmd);

		global $lng, $ilCtrl;

		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->counter = 1;
		$this->confirmdelete = $confirmdelete;
		
		$this->setFormName('codesform');
		$this->setStyle('table', 'fullwidth');

		if (!$confirmdelete)
		{
			$this->addColumn('','f','1%');
		}
		$this->addColumn($this->lng->txt("survey_code"),'code', '');
		$this->addColumn($this->lng->txt("create_date"),'date', '');
		$this->addColumn($this->lng->txt("survey_code_used"),'used', '');
		$this->addColumn($this->lng->txt("survey_code_url"),'url', '');
	
		$this->setRowTemplate("tpl.il_svy_svy_codes_row.html", "Modules/Survey");

		if ($confirmdelete)
		{
			$this->addCommandButton('deleteExportFile', $this->lng->txt('confirm'));
			$this->addCommandButton('cancelDeleteExportFile', $this->lng->txt('cancel'));
		}
		else
		{
			$this->addMultiCommand('exportCodes', $this->lng->txt('export'));
			$this->addMultiCommand('deleteCodes', $this->lng->txt('delete'));

			$languages = $lng->getInstalledLanguages();
			$data = array();
			foreach ($languages as $lang)
			{
				$data[$lang] = $this->lng->txt("lang_$lang");
			}
			global $ilUser;
			$this->addSelectionButton('lang', $data, 'setCodeLanguage', $this->lng->txt("survey_codes_lang"), $ilUser->getPref("survey_code_language"));
			$this->addCommandButton('exportAllCodes', $this->lng->txt('export_all_survey_codes'));
		}

		$this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

		$this->setDefaultOrderField("code");
		$this->setDefaultOrderDirection("asc");
		
		if ($confirmdelete)
		{
			$this->disable('sort');
			$this->disable('select_all');
		}
		else
		{
			$this->setPrefix('chb_code');
			$this->setSelectAllCheckbox('chb_code');
			$this->enable('sort');
			$this->enable('select_all');
		}
		$this->enable('header');
	}

	/**
	 * fill row 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function fillRow($data)
	{
		global $lng;
		
		if (!$this->confirmdelete)
		{
			$this->tpl->setCurrentBlock('checkbox');
			$this->tpl->setVariable('CB_CODE', $data['code']);
			$this->tpl->parseCurrentBlock();
		}
		else
		{
			$this->tpl->setCurrentBlock('hidden');
			$this->tpl->setVariable('HIDDEN_CODE', $data["code"]);
			$this->tpl->parseCurrentBlock();
		}
		if (strlen($data['href']))
		{
			$this->tpl->setCurrentBlock('url');
			$this->tpl->setVariable("URL", $data['url']);
			$this->tpl->setVariable("HREF", $data['href']);
			$this->tpl->parseCurrentBlock();
		}
		$this->tpl->setVariable("USED", ($data['used']) ? $lng->txt("used") : $lng->txt("not_used"));
		$this->tpl->setVariable("USED_CLASS", ($data['used']) ? ' smallgreen' : ' smallred');
		$this->tpl->setVariable("DATE", $data['date']);
		$this->tpl->setVariable("CODE", $data['code']);
	}
}
?>