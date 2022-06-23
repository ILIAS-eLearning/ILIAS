<?php declare(strict_types=1);

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\Refinery\Factory as RefineryFactory;
use ILIAS\HTTP\Services as HttpServices;

/**
 * BlockGUI class calendar selection.
 * @author  Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesCalendar
 */
class ilCalendarSelectionBlockGUI extends ilBlockGUI
{
    public static string $block_type = "cal_sel";

    protected const CAL_GRP_CURRENT_CONT_CONS = "curr_cont_cons";
    protected const CAL_GRP_CURRENT_CONT = "curr_cont";
    protected const CAL_GRP_PERSONAL = "personal";
    protected const CAL_GRP_OTHERS = "others";

    /**
     * @todo fix in base class
     */
    protected bool $new_rendering = true;

    protected ilTree $tree;
    protected RefineryFactory $refinery;
    protected HttpServices $http;


    protected ilDate $seed;
    protected array $calendar_groups = array();
    protected array $calendars = array();

    /**
     * @var int container ref id (0 for personal desktop)
     */
    protected int $ref_id = 0;

    /**
     * @var int container obj id (0 for personal desktop)
     */
    protected int $obj_id = 0;
    protected int $category_id = 0;

    /**
     * Constructor
     */
    public function __construct(ilDate $a_seed, int $a_ref_id = 0)
    {
        global $DIC;

        parent::__construct();
        $this->tree = $DIC->repositoryTree();
        $this->lng->loadLanguageModule('dash');
        $this->lng->loadLanguageModule('dateplaner');
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        $this->ref_id = $a_ref_id;
        $this->obj_id = ilObject::_lookupObjId($this->ref_id);
        $this->category_id = 0;
        if ($this->http->wrapper()->query()->has('category_id')) {
            $this->category_id = $this->http->wrapper()->query()->retrieve(
                'category_id',
                $this->refinery->kindlyTo()->int()
            );
        }

        $this->setLimit(5);
        $this->allow_moving = false;
        $this->seed = $a_seed;
        $this->setTitle($this->lng->txt('cal_table_categories'));

        $sel_type = ilCalendarUserSettings::_getInstance()->getCalendarSelectionType();
        $this->ctrl->setParameterByClass(
            "ilcalendarcategorygui",
            'calendar_mode',
            ilCalendarUserSettings::CAL_SELECTION_ITEMS
        );
        $this->ctrl->setParameterByClass("ilcalendarcategorygui", 'seed', $this->seed->get(IL_CAL_DATE));
        // @todo: set checked if ($sel_type == ilCalendarUserSettings::CAL_SELECTION_ITEMS)
        $this->addBlockCommand(
            $this->ctrl->getLinkTargetByClass("ilcalendarcategorygui", 'switchCalendarMode'),
            $this->lng->txt('dash_favourites')
        );
        $this->ctrl->setParameterByClass(
            "ilcalendarcategorygui",
            'calendar_mode',
            ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP
        );
        $this->ctrl->setParameterByClass("ilcalendarcategorygui", 'seed', $this->seed->get(IL_CAL_DATE));

        // @todo: set checked if ($sel_type == ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP)
        $this->addBlockCommand(
            $this->ctrl->getLinkTargetByClass("ilcalendarcategorygui", 'switchCalendarMode'),
            $this->lng->txt('dash_memberships')
        );

        $this->ctrl->setParameterByClass("ilcalendarcategorygui", 'calendar_mode', "");
        $this->addBlockCommand(
            $this->ctrl->getLinkTargetByClass("ilcalendarcategorygui", 'add'),
            $this->lng->txt('cal_add_calendar')
        );

        $this->calendar_groups = array(
            self::CAL_GRP_CURRENT_CONT_CONS => $this->lng->txt("cal_grp_" . self::CAL_GRP_CURRENT_CONT_CONS),
            self::CAL_GRP_CURRENT_CONT => $this->lng->txt("cal_grp_" . self::CAL_GRP_CURRENT_CONT),
            self::CAL_GRP_PERSONAL => $this->lng->txt("cal_grp_" . self::CAL_GRP_PERSONAL),
            self::CAL_GRP_OTHERS => $this->lng->txt("cal_grp_" . self::CAL_GRP_OTHERS)
        );

        $this->setPresentation(self::PRES_SEC_LEG);
    }

    /**
     * @inheritDoc
     */
    protected function isRepositoryObject() : bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function getBlockType() : string
    {
        return self::$block_type;
    }

    /**
     * @inheritDoc
     */
    public static function getScreenMode() : string
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];

        return IL_SCREEN_SIDE;
    }

    public function executeCommand() : string
    {
        $next_class = $this->ctrl->getNextClass();
        $cmd = $this->ctrl->getCmd("getHTML");
        switch ($next_class) {
            default:
                return $this->$cmd();
        }
    }

    /**
     * Get calendars
     */
    public function getCalendars() : void
    {
        $hidden_obj = ilCalendarVisibility::_getInstanceByUserId($this->user->getId(), $this->ref_id);
        $hidden = $hidden_obj->getHidden();
        $visible = $hidden_obj->getVisible();

        $cats = new ilCalendarCategories($this->user->getId());
        if ($this->ref_id > 0) {
            $cats->initialize(ilCalendarCategories::MODE_REPOSITORY, $this->ref_id, true);
        } elseif (ilCalendarUserSettings::_getInstance()->getCalendarSelectionType() == ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP) {
            $cats->initialize(ilCalendarCategories::MODE_PERSONAL_DESKTOP_MEMBERSHIP);
        } else {
            $cats->initialize(ilCalendarCategories::MODE_PERSONAL_DESKTOP_ITEMS);
        }

        $all = $cats->getCategoriesInfo();
        $tmp_title_counter = [];
        $categories = array();
        foreach ($all as $category) {
            //if ($category["obj_id"] == 255)
            //{var_dump($category); exit;}
            $tmp_arr['obj_id'] = (int) $category['obj_id'];
            $tmp_arr['id'] = (int) $category['cat_id'];
            $tmp_arr['hidden'] = in_array($category['cat_id'], $hidden);
            $tmp_arr['visible'] = in_array($category['cat_id'], $visible);
            $tmp_arr['title'] = (string) $category['title'];
            $tmp_arr['type'] = (string) $category['type'];
            $tmp_arr['source_ref_id'] = (int) ($category['source_ref_id'] ?? 0);

            $tmp_arr['default_selected'] = true;
            if ($this->category_id) {
                if ($this->category_id == $category['cat_id']) {
                    $tmp_arr['default_selected'] = true;
                } else {
                    $tmp_arr['default_selected'] = false;
                }
            }

            // Append object type to make type sortable
            $tmp_arr['type_sortable'] = (string) ilCalendarCategory::lookupCategorySortIndex($category['type']);
            if ($category['type'] == ilCalendarCategory::TYPE_OBJ) {
                $tmp_arr['type_sortable'] .= ('_' . ilObject::_lookupType($category['obj_id']));
            }
            $tmp_arr['color'] = (string) $category['color'];
            $tmp_arr['editable'] = (bool) $category['editable'];

            // reference
            if ($category['type'] == ilCalendarCategory::TYPE_OBJ) {
                foreach (ilObject::_getAllReferences($category['obj_id']) as $ref_id => $tmp_ref) {
                    if ($this->access->checkAccess('read', '', $ref_id)) {
                        $tmp_arr['ref_id'] = (int) $ref_id;
                    }
                }
            }

            $categories[] = $tmp_arr;

            // count title for appending the parent container if there is more than one entry.
            if (isset($tmp_title_counter[$category['type'] . '_' . $category['title']])) {
                $tmp_title_counter[$category['type'] . '_' . $category['title']]++;
            } else {
                $tmp_title_counter[$category['type'] . '_' . $category['title']] = 1;
            }
        }

        $path_categories = array();
        foreach ($categories as $cat) {
            $cat['path'] = '';
            if ($cat['type'] == ilCalendarCategory::TYPE_OBJ) {
                if (
                    isset($tmp_title_counter[$category['type'] . '_' . $category['title']]) &&
                    $tmp_title_counter[$cat['type'] . '_' . $cat['title']] > 1
                ) {
                    foreach (ilObject::_getAllReferences($cat['obj_id']) as $ref_id) {
                        $cat['path'] = $this->buildPath($ref_id);
                        break;
                    }
                }
            }
            $path_categories[] = $cat;
        }
        $path_categories = ilArrayUtil::sortArray($path_categories, 'title', "asc");

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
     */
    protected function buildPath($a_ref_id) : string
    {
        $path_arr = $this->tree->getPathFull($a_ref_id, ROOT_FOLDER_ID);
        $counter = 0;
        unset($path_arr[count($path_arr) - 1]);

        $path = '';
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

    protected function getLegacyContent() : string
    {
        $tpl = new ilTemplate("tpl.cal_selection_block_content.html", true, true, "Services/Calendar");

        foreach ($this->calendar_groups as $type => $txt) {
            foreach ($this->calendars[$type] as $c) {
                $this->renderItem($c, $tpl);
            }
            if (count($this->calendars[$type]) > 0) {
                if ($type == self::CAL_GRP_CURRENT_CONT) {
                    $txt = $this->lng->txt("cal_grp_curr_" . ilObject::_lookupType($this->obj_id));
                }
                if ($type == self::CAL_GRP_CURRENT_CONT_CONS) {
                    $txt = $this->lng->txt("cal_grp_curr_crs_cons");
                }
                $tpl->setCurrentBlock("item_grp");
                $tpl->setVariable("GRP_HEAD", $txt);
                $tpl->parseCurrentBlock();
            }
        }

        $tpl->setVariable("TXT_SHOW", $this->lng->txt("refresh"));
        $tpl->setVariable("CMD_SHOW", "saveSelection");
        $tpl->setVariable("TXT_ACTION", $this->lng->txt("select"));
        $tpl->setVariable("SRC_ACTION", ilUtil::getImagePath("arrow_downright.svg"));
        $tpl->setVariable("FORM_ACTION", $this->ctrl->getFormActionByClass("ilcalendarcategorygui"));
        $tpl->setVariable("TXT_SELECT_ALL", $this->lng->txt("select_all"));

        return $tpl->get();
    }

    protected function renderItem(array $a_set, ilTemplate $a_tpl) : void
    {
        if (strlen((string) $a_set['path'])) {
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
        } elseif ($a_set["obj_id"] == $this->obj_id) {
            // if calendar is shown and repo object id (course group given)
            $a_tpl->setVariable('VAL_CHECKED', 'checked="checked"');
            $a_tpl->setVariable('VAL_DISABLED', 'disabled');
        } elseif ($a_set['visible']) {
            $a_tpl->setVariable('VAL_CHECKED', 'checked="checked"');
        }
        $a_tpl->setVariable('BGCOLOR', $a_set['color']);

        if (
            ($a_set['type'] == ilCalendarCategory::TYPE_OBJ) &&
            $a_set['ref_id']
        ) {
            if (!$this->ref_id) {
                $this->ctrl->setParameterByClass('ilcalendarpresentationgui', 'backpd', 1);
            }
            $this->ctrl->setParameterByClass('ilcalendarpresentationgui', 'ref_id', $a_set['ref_id']);
            switch (ilObject::_lookupType($a_set['obj_id'])) {
                case 'crs':
                    $link = $this->ctrl->getLinkTargetByClass(
                        [
                            ilRepositoryGUI::class,
                            ilObjCourseGUI::class,
                            ilCalendarPresentationGUI::class
                        ],
                        ''
                    );
                    break;

                case 'grp':
                    $link = $this->ctrl->getLinkTargetByClass(
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

            $this->ctrl->clearParameterByClass(ilCalendarPresentationGUI::class, 'ref_id');

            $a_tpl->setVariable('EDIT_LINK', $link);
            $a_tpl->setVariable('VAL_TITLE', $a_set['title']);
        } elseif ($a_set['type'] == ilCalendarCategory::TYPE_OBJ) {
            $a_tpl->setVariable('PLAIN_TITLE', $a_set['title']);
        } else {
            $a_tpl->setVariable('VAL_TITLE', $a_set['title']);
            $this->ctrl->setParameterByClass("ilcalendarpresentationgui", 'category_id', $a_set['id']);
            $a_tpl->setVariable('EDIT_LINK', $this->ctrl->getLinkTargetByClass("ilcalendarpresentationgui", ''));
            $this->ctrl->setParameterByClass("ilcalendarpresentationgui", 'category_id', $this->category_id);
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
                $type = ilObject::_lookupType($a_set['obj_id']);
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
     * @inheritDoc
     */
    public function getHTML() : string
    {
        $this->getCalendars();
        return parent::getHTML();
    }

    /**
     * @inheritdoc
     */
    protected function getListItemForData(array $data) : ?\ILIAS\UI\Component\Item\Item
    {
        $factory = $this->ui->factory();
        if (isset($data["shy_button"])) {
            return $factory->item()->standard($data["shy_button"])->withDescription($data["date"]);
        } else {
            return $factory->item()->standard($data["date"]);
        }
    }
}
