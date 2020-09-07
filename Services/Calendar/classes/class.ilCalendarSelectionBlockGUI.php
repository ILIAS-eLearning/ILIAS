<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Block/classes/class.ilBlockGUI.php");

/**
 * BlockGUI class calendar selection.
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesCalendar
 */
class ilCalendarSelectionBlockGUI extends ilBlockGUI
{
    public static $block_type = "cal_sel";

    const CAL_GRP_CURRENT_CONT_CONS = "curr_cont_cons";
    const CAL_GRP_CURRENT_CONT = "curr_cont";
    const CAL_GRP_PERSONAL = "personal";
    const CAL_GRP_OTHERS = "others";


    protected $calendar_groups = array();
    protected $calendars = array();

    /**
     * @var int container ref id (0 for personal desktop)
     */
    protected $ref_id = 0;

    /**
     * @var int container obj id (0 for personal desktop)
     */
    protected $obj_id = 0;

    protected $category_id = 0;

    /**
     * Constructor
     */
    public function __construct($a_seed, $a_ref_id = 0)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        
        $this->lng = $lng;
        parent::__construct();
        $lng->loadLanguageModule('pd');
        $lng->loadLanguageModule('dateplaner');
        $this->ref_id = $a_ref_id;
        $this->obj_id = ilObject::_lookupObjId($this->ref_id);

        $this->category_id = $_GET['category_id'];
        
        $this->setLimit(5);
        $this->allow_moving = false;
        $this->seed = $a_seed;
        
        $this->setTitle($lng->txt('cal_table_categories'));
        
        include_once('./Services/Calendar/classes/class.ilCalendarUserSettings.php');
        $sel_type = ilCalendarUserSettings::_getInstance()->getCalendarSelectionType();
        $ilCtrl->setParameterByClass("ilcalendarcategorygui", 'calendar_mode', ilCalendarUserSettings::CAL_SELECTION_ITEMS);
        $ilCtrl->setParameterByClass("ilcalendarcategorygui", 'seed', $this->seed->get(IL_CAL_DATE));
        $this->addBlockCommand(
            $ilCtrl->getLinkTargetByClass("ilcalendarcategorygui", 'switchCalendarMode'),
            $lng->txt('pd_my_offers'),
            "",
            "",
            false,
            ($sel_type == ilCalendarUserSettings::CAL_SELECTION_ITEMS)
        );
        $ilCtrl->setParameterByClass("ilcalendarcategorygui", 'calendar_mode', ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP);
        $ilCtrl->setParameterByClass("ilcalendarcategorygui", 'seed', $this->seed->get(IL_CAL_DATE));
        $this->addBlockCommand(
            $ilCtrl->getLinkTargetByClass("ilcalendarcategorygui", 'switchCalendarMode'),
            $lng->txt('pd_my_memberships'),
            "",
            "",
            false,
            ($sel_type == ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP)
        );

        $ilCtrl->setParameterByClass("ilcalendarcategorygui", 'calendar_mode', "");
        $this->addBlockCommand(
            $ilCtrl->getLinkTargetByClass("ilcalendarcategorygui", 'add'),
            $lng->txt('cal_add_calendar')
        );

        $this->calendar_groups = array(
            self::CAL_GRP_CURRENT_CONT_CONS => $lng->txt("cal_grp_" . self::CAL_GRP_CURRENT_CONT_CONS),
            self::CAL_GRP_CURRENT_CONT => $lng->txt("cal_grp_" . self::CAL_GRP_CURRENT_CONT),
            self::CAL_GRP_PERSONAL => $lng->txt("cal_grp_" . self::CAL_GRP_PERSONAL),
            self::CAL_GRP_OTHERS => $lng->txt("cal_grp_" . self::CAL_GRP_OTHERS)
        );
    }

    /**
     * @inheritdoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
     * Get Screen Mode for current command.
     */
    public static function getScreenMode()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        
        return IL_SCREEN_SIDE;
    }

    /**
    * execute command
    */
    public function executeCommand()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        $next_class = $ilCtrl->getNextClass();
        $cmd = $ilCtrl->getCmd("getHTML");

        switch ($next_class) {
            default:
                return $this->$cmd();
        }
    }

    /**
     * Get calendars
     */
    public function getCalendars()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $tree = $DIC['tree'];
        $access = $DIC->access();
        
        include_once('./Services/Calendar/classes/class.ilCalendarCategories.php');
        include_once('./Services/Calendar/classes/class.ilCalendarVisibility.php');
        
        $hidden_obj = ilCalendarVisibility::_getInstanceByUserId($ilUser->getId(), $this->ref_id);

        $hidden = $hidden_obj->getHidden();
        $visible = $hidden_obj->getVisible();
        
        $cats = new ilCalendarCategories($ilUser->getId());
        if ($this->ref_id > 0) {
            $cats->initialize(ilCalendarCategories::MODE_REPOSITORY, (int) $this->ref_id, true);
        } else {
            if (ilCalendarUserSettings::_getInstance()->getCalendarSelectionType() == ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP) {
                $cats->initialize(ilCalendarCategories::MODE_PERSONAL_DESKTOP_MEMBERSHIP);
            } else {
                $cats->initialize(ilCalendarCategories::MODE_PERSONAL_DESKTOP_ITEMS);
            }
        }

        $all = $cats->getCategoriesInfo();
        $tmp_title_counter = array();
        $categories = array();
        foreach ($all as $category) {
            //if ($category["obj_id"] == 255)
            //{var_dump($category); exit;}
            $tmp_arr['obj_id'] = $category['obj_id'];
            $tmp_arr['id'] = $category['cat_id'];
            $tmp_arr['hidden'] = (bool) in_array($category['cat_id'], $hidden);
            $tmp_arr['visible'] = (bool) in_array($category['cat_id'], $visible);
            $tmp_arr['title'] = $category['title'];
            $tmp_arr['type'] = $category['type'];
            $tmp_arr['source_ref_id'] = $category['source_ref_id'];

            $tmp_arr['default_selected'] = true;
            if ($this->category_id) {
                if ($this->category_id == $category['cat_id']) {
                    $tmp_arr['default_selected'] = true;
                } else {
                    $tmp_arr['default_selected'] = false;
                }
            }

            // Append object type to make type sortable
            $tmp_arr['type_sortable'] = ilCalendarCategory::lookupCategorySortIndex($category['type']);
            if ($category['type'] == ilCalendarCategory::TYPE_OBJ) {
                $tmp_arr['type_sortable'] .= ('_' . ilObject::_lookupType($category['obj_id']));
            }
            $tmp_arr['color'] = $category['color'];
            $tmp_arr['editable'] = $category['editable'];

            // reference
            if ($category['type'] == ilCalendarCategory::TYPE_OBJ) {
                foreach (ilObject::_getAllReferences($category['obj_id']) as $ref_id => $tmp_ref) {
                    if ($access->checkAccess('read', '', $ref_id)) {
                        $tmp_arr['ref_id'] = $ref_id;
                    }
                }
            }

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
        $path_categories = ilUtil::sortArray($path_categories, 'title', "asc");


        $this->calendars[self::CAL_GRP_CURRENT_CONT_CONS] = array();
        $this->calendars[self::CAL_GRP_CURRENT_CONT] = array();
        $this->calendars[self::CAL_GRP_PERSONAL] = array();
        $this->calendars[self::CAL_GRP_OTHERS] = array();

        foreach ($path_categories as $cal) {
            if ($cal["type"] == ilCalendarCategory::TYPE_CH && $this->obj_id > 0) {
                $this->calendars[self::CAL_GRP_CURRENT_CONT_CONS][] = $cal;
            } elseif ($cal["type"] == ilCalendarCategory::TYPE_OBJ && ($this->obj_id > 0 && ($cal["obj_id"] == $this->obj_id
                || $this->ref_id == $cal["source_ref_id"]))) {
                $this->calendars[self::CAL_GRP_CURRENT_CONT][] = $cal;
            } elseif ($cal["type"] == ilCalendarCategory::TYPE_USR || $cal["type"] == ilCalendarCategory::TYPE_BOOK ||
                ($cal["type"] == ilCalendarCategory::TYPE_CH && $this->user->getId() == $cal["obj_id"])) {
                $this->calendars[self::CAL_GRP_PERSONAL][] = $cal;
            } else {
                $this->calendars[self::CAL_GRP_OTHERS][] = $cal;
            }
        }
    }

    /**
     * Build path for ref id
     *
     * @param int $a_ref_id ref id
     */
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

    
    /**
    * Fill data section
    */
    public function fillDataSection()
    {
        global $DIC;

        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        $tpl = new ilTemplate("tpl.cal_selection_block_content.html", true, true, "Services/Calendar");

        foreach ($this->calendar_groups as $type => $txt) {
            foreach ($this->calendars[$type] as $c) {
                $this->renderItem($c, $tpl);
            }
            if (count($this->calendars[$type]) > 0) {
                if ($type == self::CAL_GRP_CURRENT_CONT) {
                    $txt = $lng->txt("cal_grp_curr_" . ilObject::_lookupType($this->obj_id));
                }
                if ($type == self::CAL_GRP_CURRENT_CONT_CONS) {
                    $txt = $lng->txt("cal_grp_curr_crs_cons");
                }
                $tpl->setCurrentBlock("item_grp");
                $tpl->setVariable("GRP_HEAD", $txt);
                $tpl->parseCurrentBlock();
            }
        }
        
        $tpl->setVariable("TXT_SHOW", $lng->txt("refresh"));
        $tpl->setVariable("CMD_SHOW", "saveSelection");
        $tpl->setVariable("TXT_ACTION", $lng->txt("select"));
        $tpl->setVariable("SRC_ACTION", ilUtil::getImagePath("arrow_downright.svg"));
        $tpl->setVariable("FORM_ACTION", $ilCtrl->getFormActionByClass("ilcalendarcategorygui"));
        $tpl->setVariable("TXT_SELECT_ALL", $lng->txt("select_all"));
        
        $this->setDataSection($tpl->get());
    }

    /**
     * Render item
     *
     * @param array $a_set item datat
     */
    protected function renderItem($a_set, $a_tpl)
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $ilUser = $DIC->user();

        if (strlen($a_set['path'])) {
            $a_tpl->setCurrentBlock('calendar_path');
            $a_tpl->setVariable('ADD_PATH_INFO', $a_set['path']);
            $a_tpl->parseCurrentBlock();
        }
        
        $a_tpl->setCurrentBlock("item");
        
        $a_tpl->setVariable('VAL_ID', $a_set['id']);
        if ($this->obj_id == 0) {
            if (!$a_set['hidden'] && $a_set['default_selected']) {
                $a_tpl->setVariable('VAL_CHECKED', 'checked="checked"');
            }
        } else {						// if calendar is shown and repo object id (course group given)
            if ($a_set["obj_id"] == $this->obj_id) {
                $a_tpl->setVariable('VAL_CHECKED', 'checked="checked"');
                $a_tpl->setVariable('VAL_DISABLED', 'disabled');
            } elseif ($a_set['visible']) {
                $a_tpl->setVariable('VAL_CHECKED', 'checked="checked"');
            }
        }
        $a_tpl->setVariable('BGCOLOR', $a_set['color']);


        if (
            ($a_set['type'] == ilCalendarCategory::TYPE_OBJ) &&
            $a_set['ref_id']
        ) {
            #			if(
            #				ilCalendarCategories::_getInstance($ilUser->getId())->getMode() == ilCalendarCategories::MODE_PERSONAL_DESKTOP_MEMBERSHIP ||
            #				ilCalendarCategories::_getInstance($ilUser->getId())->getMode() == ilCalendarCategories::MODE_PERSONAL_DESKTOP_ITEMS
            #			)
            if (!$this->ref_id) {
                $ilCtrl->setParameterByClass('ilcalendarpresentationgui', 'backpd', 1);
            }
            $ilCtrl->setParameterByClass('ilcalendarpresentationgui', 'ref_id', $a_set['ref_id']);
            switch (ilObject::_lookupType($a_set['obj_id'])) {
                case 'crs':
                    $link = $ilCtrl->getLinkTargetByClass(
                        [
                            ilRepositoryGUI::class,
                            ilObjCourseGUI::class,
                            ilCalendarPresentationGUI::class
                        ],
                        ''
                    );
                    break;

                case 'grp':
                    $link = $ilCtrl->getLinkTargetByClass(
                        [
                            ilRepositoryGUI::class,
                            ilObjGroupGUI::class,
                            ilCalendarPresentationGUI::class
                        ],
                        ''
                    );
                    break;

                default:
                    $link = ilLink::_getLink($a_set['ref_id']);
                    break;
            }

            $ilCtrl->clearParameterByClass(ilCalendarPresentationGUI::class, 'ref_id');

            $a_tpl->setVariable('EDIT_LINK', $link);
            $a_tpl->setVariable('VAL_TITLE', $a_set['title']);
        } elseif ($a_set['type'] == ilCalendarCategory::TYPE_OBJ) {
            $a_tpl->setVariable('PLAIN_TITLE', $a_set['title']);
        } else {
            $a_tpl->setVariable('VAL_TITLE', $a_set['title']);
            $ilCtrl->setParameterByClass("ilcalendarpresentationgui", 'category_id', $a_set['id']);
            $a_tpl->setVariable('EDIT_LINK', $ilCtrl->getLinkTargetByClass("ilcalendarpresentationgui", ''));
            $ilCtrl->setParameterByClass("ilcalendarpresentationgui", 'category_id', $_GET["category_id"]);
            $a_tpl->setVariable('TXT_EDIT', $this->lng->txt('edit'));
        }

        switch ($a_set['type']) {
            case ilCalendarCategory::TYPE_GLOBAL:
                $a_tpl->setVariable('IMG_SRC', ilUtil::getImagePath('icon_calg.svg'));
                $a_tpl->setVariable('IMG_ALT', $this->lng->txt('cal_type_system'));
                break;
                
            case ilCalendarCategory::TYPE_USR:
                $a_tpl->setVariable('IMG_SRC', ilUtil::getImagePath('icon_usr.svg'));
                $a_tpl->setVariable('IMG_ALT', $this->lng->txt('cal_type_personal'));
                break;
            
            case ilCalendarCategory::TYPE_OBJ:
                $type = ilObject::_lookupType($a_set['obj_id']);
                $a_tpl->setVariable('IMG_SRC', ilUtil::getImagePath('icon_' . $type . '.svg'));
                $a_tpl->setVariable('IMG_ALT', $this->lng->txt('cal_type_' . $type));
                break;

            case ilCalendarCategory::TYPE_BOOK:
                $a_tpl->setVariable('IMG_SRC', ilUtil::getImagePath('icon_book.svg'));
                $a_tpl->setVariable('IMG_ALT', $this->lng->txt('cal_type_' . $type));
                break;

            case ilCalendarCategory::TYPE_CH:
                $a_tpl->setVariable('IMG_SRC', ilUtil::getImagePath('icon_calch.svg'));
                $a_tpl->setVariable('IMG_ALT', $this->lng->txt('cal_ch_ch'));
                break;
        }
        
        $a_tpl->parseCurrentBlock();
    }

    /**
     * Get block HTML code.
     */
    public function getHTML()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];
        $ilSetting = $DIC['ilSetting'];
        
        $this->getCalendars();
        
        return parent::getHTML();
    }
}
