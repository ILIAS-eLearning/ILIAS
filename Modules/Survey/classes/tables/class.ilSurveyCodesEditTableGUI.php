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

class ilSurveyCodesEditTableGUI extends ilTable2GUI
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

        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
    
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        $this->addColumn($this->lng->txt("survey_code"), 'code', '');
        $this->addColumn($this->lng->txt("email"), 'email', '');
        $this->addColumn($this->lng->txt("lastname"), 'last_name', '');
        $this->addColumn($this->lng->txt("firstname"), 'first_name', '');
        $this->addColumn($this->lng->txt("mail_sent_short"), 'sent', '');
        
        $this->setRowTemplate("tpl.il_svy_svy_codes_edit_row.html", "Modules/Survey");

        $this->addCommandButton('updateCodes', $this->lng->txt('save'));
        $this->addCommandButton('codes', $this->lng->txt('cancel'));

        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, $a_parent_cmd));

        $this->setDefaultOrderField("code");
        $this->setDefaultOrderDirection("asc");
    }

    public function fillRow($data)
    {
        $this->tpl->setVariable('ID', $data["id"]);
        $this->tpl->setVariable("SENT", ($data['sent']) ?  ' checked="checked"' : '');
        $this->tpl->setVariable("CODE", $data['code']);
        $this->tpl->setVariable("EMAIL", $data['email']);
        $this->tpl->setVariable("LAST_NAME", $data['last_name']);
        $this->tpl->setVariable("FIRST_NAME", $data['first_name']);
    }
}
