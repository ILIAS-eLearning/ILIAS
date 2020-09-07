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
    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        parent::__construct($a_parent_obj, $a_parent_cmd);

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();

        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        $this->setFormName('codesform');
        
        $this->addColumn('', '', '1%');
        $this->addColumn($this->lng->txt("survey_code"), 'code', '');
        $this->addColumn($this->lng->txt("email"), 'email', '');
        $this->addColumn($this->lng->txt("lastname"), 'last_name', '');
        $this->addColumn($this->lng->txt("firstname"), 'first_name', '');
        $this->addColumn($this->lng->txt("create_date"), 'date', '');
        $this->addColumn($this->lng->txt("survey_code_used"), 'used', '');
        $this->addColumn($this->lng->txt("mail_sent_short"), 'sent', '');
        $this->addColumn($this->lng->txt("survey_code_url"));
    
        $this->setRowTemplate("tpl.il_svy_svy_codes_row.html", "Modules/Survey");

        $this->addMultiCommand('editCodes', $this->lng->txt('edit'));
        $this->addMultiCommand('exportCodes', $this->lng->txt('export'));
        $this->addMultiCommand('deleteCodesConfirm', $this->lng->txt('delete'));
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        include_once "Services/UIComponent/Button/classes/class.ilSubmitButton.php";
        $button = ilSubmitButton::getInstance();
        $button->setCaption("export_all_survey_codes");
        $button->setCommand("exportAllCodes");
        $button->setOmitPreventDoubleSubmission(true);
        $this->addCommandButtonInstance($button);
    
        $this->setDefaultOrderField("code");
        $this->setDefaultOrderDirection("asc");

        $this->setPrefix('chb_code');
        $this->setSelectAllCheckbox('chb_code');
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
        $lng = $this->lng;
                
        $this->tpl->setVariable('CB_CODE', $data['id']);
    
        // :TODO: see permalink gui
        if (strlen($data['href'])) {
            $this->tpl->setCurrentBlock('url');
            $this->tpl->setVariable("URL", $lng->txt("survey_code_url_name"));
            $this->tpl->setVariable("HREF", $data['href']);
            $this->tpl->parseCurrentBlock();
        }
        
        $this->tpl->setVariable("USED", ($data['used']) ? $lng->txt("used") : $lng->txt("not_used"));
        $this->tpl->setVariable("SENT", ($data['sent']) ?  '&#10003;' : '');
        $this->tpl->setVariable("USED_CLASS", ($data['used']) ? ' smallgreen' : ' smallred');
        $this->tpl->setVariable("DATE", ilDatePresentation::formatDate(new ilDateTime($data['date'], IL_CAL_UNIX)));
        $this->tpl->setVariable("CODE", $data['code']);
        $this->tpl->setVariable("EMAIL", $data['email']);
        $this->tpl->setVariable("LAST_NAME", $data['last_name']);
        $this->tpl->setVariable("FIRST_NAME", $data['first_name']);
    }
}
