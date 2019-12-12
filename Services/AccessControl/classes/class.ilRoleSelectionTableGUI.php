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

include_once('Services/Table/classes/class.ilTable2GUI.php');

/**
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ServicesAccessControl
*/
class ilRoleSelectionTableGUI extends ilTable2GUI
{
    
    /**
     *
     * @return
     * @param object $a_parent_obj
     * @param object $a_parent_cmd
     */
    public function __construct($a_parent_obj, $a_parent_cmd)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addColumn('', 'f', 1);
        $this->addColumn($this->lng->txt('title'), 'title', "70%");
        $this->addColumn($this->lng->txt('context'), 'context', "30%");
        
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.show_role_selection_row.html", "Services/AccessControl");
        $this->setDefaultOrderField('type');
        $this->setDefaultOrderDirection("desc");
    }
    
    
    /**
     * Fill row
     *
     * @access public
     * @param array row data
     *
     */
    public function fillRow($a_set)
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        if (strlen($a_set['description'])) {
            $this->tpl->setVariable('VAL_DESC', $a_set['description']);
        }
        
        $this->tpl->setVariable('VAL_CONTEXT', $a_set['context']);
    }
    

    /**
     * Parse Search entries
     *
     * @access public
     * @param array array of search entries
     *
     */
    public function parse($entries)
    {
        global $DIC;

        $rbacreview = $DIC['rbacreview'];
        
        include_once './Services/AccessControl/classes/class.ilObjRole.php';
        foreach ($entries as $entry) {
            $tmp_arr['id'] = $entry['obj_id'];
            $tmp_arr['title'] = ilObjRole::_getTranslation(ilObject::_lookupTitle($entry['obj_id']));
            $tmp_arr['description'] = ilObject::_lookupDescription($entry['obj_id']);
            $tmp_arr['context'] = ilObject::_lookupTitle($rbacreview->getObjectOfRole($entry['obj_id']));

            $records_arr[] = $tmp_arr;
        }
        
        $this->setData($records_arr ? $records_arr : array());
    }
}
