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
* @version $Id: class.ilSurveyCodesTableGUI.php 20638 2009-07-19 08:14:34Z hschottm $
*
* @ingroup ModulesSurvey
*/

class ilSurveyCodesMailTableGUI extends ilTable2GUI
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
		$this->addColumn($this->lng->txt("email"),'email', '');
		$this->addColumn($this->lng->txt("mail_sent_short"),'sent', '');
	
		$this->setRowTemplate("tpl.il_svy_svy_codes_mail_row.html", "Modules/Survey");

		if ($confirmdelete)
		{
			$this->addCommandButton('deleteExternalMailRecipients', $this->lng->txt('confirm'));
			$this->addCommandButton('cancelDeleteExternalMailRecipients', $this->lng->txt('cancel'));
		}
		else
		{
			$this->addMultiCommand('deleteInternalMailRecipient', $this->lng->txt('delete'));
//			$this->addCommandButton('addInternalMailRecipient', $this->lng->txt('add'));
			$this->addCommandButton('importExternalMailRecipients', $this->lng->txt('import'));
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
			$this->setPrefix('chb_ext');
			$this->setSelectAllCheckbox('chb_ext');
			$this->enable('sort');
			$this->enable('select_all');
		}
		$this->enable('header');
	}
	
	public function completeColumns()
	{
		if (is_array($this->row_data))
		{
			if (array_key_exists(0, $this->row_data) && is_array($this->row_data[0]))
			{
				foreach ($this->row_data[0] as $key => $value)
				{
					if (strcmp($key, 'email') != 0 && strcmp($key, 'code') != 0 && strcmp($key, 'sent') != 0)
					{
						$this->addColumn($key,$key,'');
					}
				}
			}
		}
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

		$this->tpl->setVariable('EMAIL', $data['email']);
		$this->tpl->setVariable('SENT', ($data['sent']) ? '&#10003;' : '');
		$this->tpl->setVariable('CB_CODE', $data['code']);
		foreach ($data as $key => $value)
		{
			if (strcmp($key, 'email') != 0 && strcmp($key, 'code') != 0 && strcmp($key, 'sent') != 0)
			{
				$this->tpl->setCurrentBlock('column');
				$this->tpl->setVariable('COLUMN', $value);
				$this->tpl->parseCurrentBlock();
			}
		}
	}
}
?>