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
* show list of alle calendars to manage
*
* @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
* @version $Id$
*
* @ingroup ServicesCalendar
*/

class ilCalendarManageTableGUI extends ilTable2GUI
{
    /**
     * @var ilCalendarActions
     */
    protected $actions;

    /**
     * Constructor
     *
     * @access public
     * @param
     * @return
     */
    public function __construct($a_parent_obj)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];

        $this->setId("calmng");

        include_once("./Services/Calendar/classes/class.ilCalendarActions.php");
        $this->actions = ilCalendarActions::getInstance();

        $this->lng = $lng;
        $this->lng->loadLanguageModule('dateplaner');
        $this->ctrl = $ilCtrl;
        
        parent::__construct($a_parent_obj, 'manage');
        $this->setFormName('categories');
        $this->addColumn('', '', '1px', true);
        $this->addColumn($this->lng->txt('type'), 'type_sortable', '1%');
        $this->addColumn($this->lng->txt('title'), 'title', '79%');
        $this->addColumn('', '', '20%');
        
        $this->setRowTemplate("tpl.manage_row.html", "Services/Calendar");
        $this->setFormAction($this->ctrl->getFormAction($a_parent_obj, "manage"));
        
        $this->enable('select_all');
        $this->enable('sort');
        $this->enable('header');
        $this->enable('num_info');
        
        $this->setSelectAllCheckbox('selected_cat_ids');
        $this->setShowRowsSelector(true);
        // $this->setDisplayAsBlock(true);


        /*
        $title = $this->lng->txt('cal_table_categories');
        $title .= $this->appendCalendarSelection();
        $table_gui->setTitle($title);
        */

        $this->addMultiCommand('confirmDelete', $this->lng->txt('delete'));
        // $this->addCommandButton('add',$this->lng->txt('add'));

        $this->setDefaultOrderDirection('asc');
        $this->setDefaultOrderField('type_sortable');
    }
    
    /**
     * reset table to defaults
     */
    public function resetToDefaults()
    {
        $this->resetOffset();
        $this->setOrderField('type_sortable');
        $this->setOrderDirection('asc');
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
        include_once("./Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
        $current_selection_list = new ilAdvancedSelectionListGUI();
        $current_selection_list->setListTitle($this->lng->txt("actions"));
        $current_selection_list->setId("act_" . $a_set['id']);
        
        $this->ctrl->setParameter($this->getParentObject(), 'category_id', $a_set['id']);

        // edit
        if ($this->actions->checkSettingsCal($a_set['id'])) {
            $url = $this->ctrl->getLinkTarget($this->getParentObject(), 'edit');
            $current_selection_list->addItem($this->lng->txt('settings'), '', $url);
        }

        // import (ics appointments)
        if ($this->actions->checkAddEvent($a_set['id'])) {
            $url = $this->ctrl->getLinkTarget($this->getParentObject(), 'importAppointments');
            $current_selection_list->addItem($this->lng->txt('cal_import_appointments'), '', $url);
        }

        // unshare
        if ($this->actions->checkUnshareCal($a_set['id'])) {
            $url = $this->ctrl->getLinkTarget($this->getParentObject(), 'unshare');
            $current_selection_list->addItem($this->lng->txt('cal_unshare'), '', $url);
        }

        // share
        if ($this->actions->checkShareCal($a_set['id'])) {
            $url = $this->ctrl->getLinkTarget($this->getParentObject(), 'shareSearch');
            $current_selection_list->addItem($this->lng->txt('cal_share'), '', $url);
        }

        // synchronize
        if ($this->actions->checkSynchronizeCal($a_set['id'])) {
            $url = $this->ctrl->getLinkTarget($this->getParentObject(), 'synchroniseCalendar');
            $current_selection_list->addItem($this->lng->txt('cal_cal_synchronize'), '', $url);
        }

        // delete
        if ($this->actions->checkDeleteCal($a_set['id'])) {
            $url = $this->ctrl->getLinkTarget($this->getParentObject(), 'confirmDelete');
            $current_selection_list->addItem($this->lng->txt('delete'), '', $url);

            $this->tpl->setCurrentBlock("checkbox");
            $this->tpl->setVariable('VAL_ID', $a_set['id']);
            $this->tpl->parseCurrentBlock();
        }

        $this->ctrl->setParameter($this->getParentObject(), 'category_id', '');

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
                
            case ilCalendarCategory::TYPE_BOOK:
                $this->tpl->setVariable('IMG_SRC', ilUtil::getImagePath('icon_book.svg'));
                $this->tpl->setVariable('IMG_ALT', $this->lng->txt('cal_type_' . $type));
                break;

            case ilCalendarCategory::TYPE_CH:
                $this->tpl->setVariable('IMG_SRC', ilUtil::getImagePath('icon_calch.svg'));
                $this->tpl->setVariable('IMG_ALT', $this->lng->txt('cal_ch_ch'));
                break;

        }
        
        $this->tpl->setVariable('VAL_TITLE', $a_set['title']);
        $this->ctrl->setParameterByClass(ilCalendarPresentationGUI::class, 'backvm', 1);
        $this->ctrl->setParameterByClass(
            "ilcalendarpresentationgui",
            'category_id',
            $a_set['id']
        );
        $this->tpl->setVariable(
            'EDIT_LINK',
            $this->ctrl->getLinkTargetByClass(
                "ilcalendarpresentationgui",
                ''
            )
        );
        $this->ctrl->setParameterByClass("ilcalendarpresentationgui", 'category_id', $_GET["category_id"]);

        $this->tpl->setVariable('BGCOLOR', $a_set['color']);
        $this->tpl->setVariable("ACTIONS", $current_selection_list->getHTML());
        
        /*		if(strlen($a_set['path']))
                {
                    $this->tpl->setCurrentBlock('calendar_path');
                    $this->tpl->setVariable('ADD_PATH_INFO',$a_set['path']);
                    $this->tpl->parseCurrentBlock();
                }*/
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
        $cats = ilCalendarCategories::_getInstance($ilUser->getId());
        //$cats->initialize(ilCalendarCategories::MODE_MANAGE);
    
        $tmp_title_counter = array();
        $categories = array();
        foreach ($cats->getCategoriesInfo() as $category) {
            $tmp_arr['obj_id'] = $category['obj_id'];
            $tmp_arr['id'] = $category['cat_id'];
            $tmp_arr['title'] = $category['title'];
            $tmp_arr['type'] = $category['type'];

            // Append object type to make type sortable
            $tmp_arr['type_sortable'] = ilCalendarCategory::lookupCategorySortIndex($category['type']);
            if ($category['type'] == ilCalendarCategory::TYPE_OBJ) {
                $tmp_arr['type_sortable'] .= ('_' . ilObject::_lookupType($category['obj_id']));
            }

            $tmp_arr['color'] = $category['color'];
            $tmp_arr['editable'] = $category['editable'];
            $tmp_arr['accepted'] = $category['accepted'];
            $tmp_arr['remote'] = $category['remote'];

            $categories[] = $tmp_arr;
            
            // count title for appending the parent container if there is more than one entry.
            $tmp_title_counter[$category['type'] . '_' . $category['title']]++;
        }
        
        $path_categories = array();
        foreach ($categories as $cat) {
            if ($cat['type'] == ilCalendarCategory::TYPE_OBJ) {
                if ($tmp_title_counter[$cat['type'] . '_' . $cat['title']] > 1) {
                    foreach (ilObject::_getAllReferences($cat['obj_id']) as $ref_id) {
                        include_once './Services/Tree/classes/class.ilPathGUI.php';
                        $path = new ilPathGUI();
                        $path->setUseImages(false);
                        $path->enableTextOnly(false);
                        $cat['path'] = $path->getPath(ROOT_FOLDER_ID, $ref_id);
                        break;
                    }
                }
            }
            $path_categories[] = $cat;
        }
        $this->setData($path_categories);
    }
}
