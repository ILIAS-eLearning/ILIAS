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

include_once './Services/Table/classes/class.ilTable2GUI.php';
include_once './Services/Search/classes/Lucene/class.ilLuceneAdvancedSearchFields.php';

/**
* Activation of meta data fields
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup
*/
class ilLuceneAdvancedSearchActivationTableGUI extends ilTable2GUI
{
    /**
     * constructor
     *
     * @access public
     * @param
     *
     */
    public function __construct($a_parent_obj, $a_parent_cmd = '')
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->lng = $lng;
        $this->ctrl = $ilCtrl;
        
        parent::__construct($a_parent_obj, $a_parent_cmd);
        $this->addColumn('', 'id', '0px');
        $this->addColumn($this->lng->txt('title'), 'title', '60%');
        $this->addColumn($this->lng->txt('type'), 'type', '40%');
        $this->setRowTemplate('tpl.lucene_activation_row.html', 'Services/Search');
        $this->disable('sort');
        $this->setLimit(100);
        $this->setSelectAllCheckbox('fid');
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        
        $access = $GLOBALS['DIC']->access();
        if ($access->checkAccess('write', '', $this->getParentObject()->object->getRefId())) {
            $this->addMultiCommand('saveAdvancedLuceneSettings', $this->lng->txt('lucene_activate_field'));
        }
    }
    
    /**
     * Fill template row
     */
    public function fillRow($a_set)
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        $this->tpl->setVariable('VAL_CHECKED', $a_set['active'] ? 'checked="checked"' : '');
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        $this->tpl->setVariable('VAL_TYPE', $a_set['type']);
    }
    
    public function parse(ilLuceneAdvancedSearchSettings $settings)
    {
        foreach (ilLuceneAdvancedSearchFields::getFields() as $field => $translation) {
            $tmp_arr['id'] = $field;
            $tmp_arr['active'] = $settings->isActive($field);
            $tmp_arr['title'] = $translation;
            
            $tmp_arr['type'] = (substr($field, 0, 3) == 'lom') ?
                $this->lng->txt('search_lom') :
                $this->lng->txt('search_adv_md');
            
            $content[] = $tmp_arr;
        }
        $this->setData($content ? $content : array());
    }
}
