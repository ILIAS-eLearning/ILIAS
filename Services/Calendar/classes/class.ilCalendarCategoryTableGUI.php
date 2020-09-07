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
* show presentation of calendar category side block
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @ingroup ServicesCalendar
*/

class ilCalendarCategoryTableGUI extends ilTable2GUI
{
    private $seed = null;
    
    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj, ilDateTime $seed = null)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];

        // this should be deprecated
        die("ilCalendarCategoryTableGUI::_construct");


        $this->lng = $lng;
        $this->lng->loadLanguageModule('dateplaner');
        $this->ctrl = $ilCtrl;
        
        $this->seed = $seed;

        $this->setId('calmng');
        
        parent::__construct($a_parent_obj, 'showCategories');
        $this->setFormName('categories');
        $this->addColumn('', '', "1", true);
        $this->addColumn($this->lng->txt('type'), 'type_sortable', "1");
        $this->addColumn($this->lng->txt('title'), 'title', "100%");
        $this->addColumn('', 'subscription', '');
        
        $this->ctrl->setParameterByClass(get_class($this->getParentObject()), 'seed', $this->seed->get(IL_CAL_DATE));
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj));
        $this->setRowTemplate("tpl.show_category_row.html", "Services/Calendar");
        $this->disable('sort');
        if (!$ilUser->prefs["screen_reader_optimization"]) {
            $this->disable('header');
        }

        //$this->setShowRowsSelector(true);
        $this->disable('numinfo');
        $this->enable('select_all');
        $this->setSelectAllCheckbox('selected_cat_ids');
        $this->setDisplayAsBlock(true);

        $this->setDefaultOrderDirection('asc');
        $this->setDefaultOrderField('type_sortable');
        
        // Show add calendar button
        $this->addCommandButton('add', $this->lng->txt('cal_add_calendar'));
    }
    
    /**
     * fill row
     *
     * @access protected
     * @param
     * @return
     */
    protected function fillRow($a_set)
    {
        $this->tpl->setVariable('VAL_ID', $a_set['id']);
        if (!$a_set['hidden']) {
            $this->tpl->setVariable('VAL_CHECKED', 'checked="checked"');
        }
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        $this->tpl->setVariable('BGCOLOR', $a_set['color']);
        
        $this->ctrl->setParameter($this->getParentObject(), 'category_id', $a_set['id']);
        $this->tpl->setVariable('EDIT_LINK', $this->ctrl->getLinkTarget($this->getParentObject(), 'details'));
        $this->tpl->setVariable('TXT_EDIT', $this->lng->txt('edit'));

        switch ($a_set['type']) {
            case ilCalendarCategory::TYPE_GLOBAL:
                $this->tpl->setVariable('IMG_SRC', ilUtil::getImagePath('icon_calg.svg'));
                $this->tpl->setVariable('IMG_ALT', $this->lng->txt('cal_type_system'));
                break;
                
            case ilCalendarCategory::TYPE_USR:
                $this->tpl->setVariable('IMG_SRC', ilUtil::getImagePath('icon_usr.svg'));
                $this->tpl->setVariable('IMG_ALT', $this->lng->txt('cal_type_personal'));
                break;
            
            case ilCalendarCategory::TYPE_OBJ:
                $type = ilObject::_lookupType($a_set['obj_id']);
                $this->tpl->setVariable('IMG_SRC', ilUtil::getImagePath('icon_' . $type . '.svg'));
                $this->tpl->setVariable('IMG_ALT', $this->lng->txt('cal_type_' . $type));
                break;
        }
        if (strlen($a_set['path'])) {
            $this->tpl->setCurrentBlock('calendar_path');
            $this->tpl->setVariable('ADD_PATH_INFO', $a_set['path']);
            $this->tpl->parseCurrentBlock();
        }

        // Subscription link
        $this->tpl->setVariable('SUB_SRC', ilRSSButtonGUI::get(ilRSSButtonGUI::ICON_ICAL));
        $this->ctrl->setParameterByClass('ilcalendarsubscriptiongui', 'seed', $this->seed->get(IL_CAL_DATE));
        $this->ctrl->setParameterByClass('ilcalendarsubscriptiongui', 'category_id', $a_set['id']);
        $this->tpl->setVariable('SUB_LINK', $this->ctrl->getLinkTargetByClass(array('ilcalendarpresentationgui','ilcalendarsubscriptiongui')));
        $this->ctrl->setParameterByClass('ilcalendarsubscriptiongui', 'category_id', "");
        $this->tpl->setVariable('SUB_ALT', $this->lng->txt('ical_export'));
    }
    
    /**
     * parse
     *
     * @access public
     * @return
     */
    public function parse()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $tree = $DIC['tree'];
        
        include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
        include_once('./Services/Calendar/classes/class.ilCalendarVisibility.php');
        
        $hidden_obj = ilCalendarVisibility::_getInstanceByUserId($ilUser->getId());
        $hidden = $hidden_obj->getHidden();
        
        $cats = ilCalendarCategories::_getInstance($ilUser->getId());
        $all = $cats->getCategoriesInfo();
        $tmp_title_counter = array();
        $categories = array();
        foreach ($all as $category) {
            $tmp_arr['obj_id'] = $category['obj_id'];
            $tmp_arr['id'] = $category['cat_id'];
            $tmp_arr['hidden'] = (bool) in_array($category['cat_id'], $hidden);
            $tmp_arr['title'] = $category['title'];
            $tmp_arr['type'] = $category['type'];
            
            // Append object type to make type sortable
            $tmp_arr['type_sortable'] = ilCalendarCategory::lookupCategorySortIndex($category['type']);
            if ($category['type'] == ilCalendarCategory::TYPE_OBJ) {
                $tmp_arr['type_sortable'] .= ('_' . ilObject::_lookupType($category['obj_id']));
            }
            
            $tmp_arr['color'] = $category['color'];
            $tmp_arr['editable'] = $category['editable'];
            
            $categories[] = $tmp_arr;
            
            // count title for appending the parent container if there is more than one entry.
            $tmp_title_counter[$category['type'] . '_' . $category['title']]++;
        }
        
        $path_categories = array();
        foreach ($categories as $cat) {
            if ($cat['type'] == ilCalendarCategory::TYPE_OBJ) {
                if ($tmp_title_counter[$cat['type'] . '_' . $cat['title']] > 1) {
                    foreach (ilObject::_getAllReferences($cat['obj_id']) as $ref_id) {
                        $cat['path'] = $this->buildPath($ref_id);
                        break;
                    }
                }
            }
            $path_categories[] = $cat;
        }
        $this->setData($path_categories);
    }
    
    protected function buildPath($a_ref_id)
    {
        global $DIC;

        $tree = $DIC['tree'];

        $path_arr = $tree->getPathFull($a_ref_id, ROOT_FOLDER_ID);
        $counter = 0;
        unset($path_arr[count($path_arr) - 1]);

        foreach ($path_arr as $data) {
            if ($counter++) {
                $path .= " -> ";
            }
            $path .= $data['title'];
        }
        if (strlen($path) > 30) {
            return '...' . substr($path, -30);
        }
        return $path;
    }
}
