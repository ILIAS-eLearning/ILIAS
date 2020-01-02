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

include_once("./Services/Table/classes/class.ilTable2GUI.php");

/**
* TableGUI class for special users in survey administration
*
* @author Helmut Schottmüller <helmut.schottmueller@mac.com>
* @version $Id$
*
* @ingroup ModulesSurvey
*
*/
class ilSpecialUsersTableGUI extends ilTable2GUI
{
    public function __construct($a_parent_obj, $a_parent_cmd = "")
    {
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        
        $this->addColumn("", "f", "1");
        $this->addColumn($lng->txt("login"), "", "33%");
        $this->addColumn($lng->txt("firstname"), "", "33%");
        $this->addColumn($lng->txt("lastname"), "", "33%");
        $this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.table_special_users_row.html", "Modules/Survey");
        $this->setDefaultOrderField("lastname");
        $this->setDefaultOrderDirection("asc");
    }
    
    /**
    * Standard Version of Fill Row. Most likely to
    * be overwritten by derived class.
    */
    protected function fillRow($a_set)
    {
        $ilCtrl = $this->ctrl;
        include_once "./Services/User/classes/class.ilObjUser.php";
        $user = ilObjUser::_lookupFields($a_set);
        $ilCtrl->setParameterByClass("ilObjSurveyAdministrationGUI", "item_id", $user["usr_id"]);
        $this->tpl->setVariable("USER_ID", $user["usr_id"]);
        $this->tpl->setVariable("LOGIN", $user["login"]);
        $this->tpl->setVariable("FIRSTNAME", $user["firstname"]);
        $this->tpl->setVariable("LASTNAME", $user["lastname"]);
    }
}
