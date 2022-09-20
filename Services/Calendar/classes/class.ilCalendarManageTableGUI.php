<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * show list of alle calendars to manage
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesCalendar
 */
class ilCalendarManageTableGUI extends ilTable2GUI
{
    protected ilCalendarActions $actions;
    protected ilObjUser $user;

    public function __construct(object $a_parent_obj)
    {
        global $DIC;

        $this->setId("calmng");
        parent::__construct($a_parent_obj, 'manage');

        $this->user = $DIC->user();

        $this->actions = ilCalendarActions::getInstance();
        $this->lng->loadLanguageModule('dateplaner');
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
        $this->addMultiCommand('confirmDelete', $this->lng->txt('delete'));
        $this->setDefaultOrderDirection('asc');
        $this->setDefaultOrderField('type_sortable');
    }

    /**
     * reset table to defaults
     */
    public function resetToDefaults(): void
    {
        $this->resetOffset();
        $this->setOrderField('type_sortable');
        $this->setOrderDirection('asc');
    }

    protected function fillRow(array $a_set): void
    {
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
                $type = ilObject::_lookupType($a_set['obj_id']);
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
        $this->tpl->setVariable('BGCOLOR', $a_set['color']);
        $this->tpl->setVariable("ACTIONS", $current_selection_list->getHTML());
    }

    public function parse(): void
    {
        $cats = ilCalendarCategories::_getInstance($this->user->getId());

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
            if ($tmp_title_counter[$category['type'] . '_' . $category['title']] ?? false) {
                $tmp_title_counter[$category['type'] . '_' . $category['title']]++;
            } else {
                $tmp_title_counter[$category['type'] . '_' . $category['title']] = 1;
            }
        }

        $path_categories = array();
        foreach ($categories as $cat) {
            if ($cat['type'] == ilCalendarCategory::TYPE_OBJ) {
                if (($tmp_title_counter[$cat['type'] . '_' . $cat['title']] ?? 1) > 1) {
                    foreach (ilObject::_getAllReferences($cat['obj_id']) as $ref_id) {
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
