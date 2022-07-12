<?php declare(strict_types=1);

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

use ILIAS\Modules\EmployeeTalk\TalkSeries\Repository\IliasDBEmployeeTalkSeriesRepository;

/**
 * class for calendar categories
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesCalendar
 */
class ilCalendarCategories
{
    public const MODE_UNDEFINED = 0;
    public const MODE_REPOSITORY = 2;                        // course/group full calendar view (allows to select other calendars)
    public const MODE_REMOTE_ACCESS = 3;
    public const MODE_PERSONAL_DESKTOP_MEMBERSHIP = 4;
    public const MODE_PERSONAL_DESKTOP_ITEMS = 5;
    public const MODE_MANAGE = 6;
    public const MODE_CONSULTATION = 7;
    public const MODE_PORTFOLIO_CONSULTATION = 8;
    public const MODE_REMOTE_SELECTED = 9;
    public const MODE_REPOSITORY_CONTAINER_ONLY = 10;        // course/group content view (side block, focus on course/group appointments only)
    public const MODE_SINGLE_CALENDAR = 11;

    protected static ?ilCalendarCategories $instance = null;
    protected ilDBInterface $db;
    protected ilLogger $logger;
    protected ilFavouritesDBRepository $fav_rep;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected ilRbacSystem $rbacsystem;
    protected ilAccessHandler $access;
    protected ilTree $tree;

    protected int $user_id = 0;
    protected int $mode = self::MODE_UNDEFINED;

    protected array $categories = array();
    protected array $categories_info = array();
    protected array $subitem_categories = array();

    protected int $root_ref_id = 0;
    protected int $root_obj_id = 0;

    protected int $ch_user_id = 0;
    protected int $target_ref_id = 0;

    /**
     * Singleton instance
     */
    public function __construct(int $a_usr_id = 0)
    {
        global $DIC;

        $this->user = $DIC->user();
        $this->db = $DIC->database();
        $this->logger = $DIC->logger()->cal();
        $this->lng = $DIC->language();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->access = $DIC->access();
        $this->user_id = $a_usr_id;
        $this->tree = $DIC->repositoryTree();
        if (!$this->user_id) {
            $this->user_id = $this->user->getId();
        }
        $this->fav_rep = new ilFavouritesDBRepository();
    }

    /**
     * get singleton instance
     */
    public static function _getInstance($a_usr_id = 0) : ilCalendarCategories
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilCalendarCategories($a_usr_id);
    }

    /**
     * lookup category by obj_id
     */
    public static function _lookupCategoryIdByObjId(int $a_obj_id) : int
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT cat_id FROM cal_categories  " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND type = " . $ilDB->quote(ilCalendarCategory::TYPE_OBJ, 'integer') . " ";

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->cat_id;
        }
        return 0;
    }

    /**
     * check if user is owner of a category
     */
    public static function _isOwner(int $a_usr_id, int $a_cal_id) : bool
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM cal_categories " .
            "WHERE cat_id = " . $ilDB->quote($a_cal_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            "AND type = " . $ilDB->quote(ilCalendarCategory::TYPE_USR, 'integer') . " ";
        $res = $ilDB->query($query);
        return (bool) $res->numRows();
    }

    /**
     * Delete cache (add remove desktop item)
     */
    public static function deletePDItemsCache(int $a_usr_id) : void
    {
        ilCalendarCache::getInstance()->deleteByAdditionalKeys(
            $a_usr_id,
            self::MODE_PERSONAL_DESKTOP_ITEMS,
            'categories'
        );
    }

    /**
     * Delete cache
     */
    public static function deleteRepositoryCache(int $a_usr_id) : void
    {
        ilCalendarCache::getInstance()->deleteByAdditionalKeys(
            $a_usr_id,
            self::MODE_REPOSITORY,
            'categories'
        );
    }

    /**
     * Serialize categories
     */
    final protected function sleep() : string
    {
        return serialize(
            array(
                'categories' => $this->categories,
                'categories_info' => $this->categories_info,
                'subitem_categories' => $this->subitem_categories
            )
        );
    }

    /**
     * Load from serialize string
     */
    protected function wakeup(string $a_ser) : void
    {
        $info = unserialize($a_ser);

        $this->categories = $info['categories'];
        $this->categories_info = $info['categories_info'];
        $this->subitem_categories = $info['subitem_categories'];
    }

    /**
     * Set ch user id
     */
    public function setCHUserId(int $a_user_id) : void
    {
        $this->ch_user_id = $a_user_id;
    }

    public function getCHUserId() : int
    {
        return $this->ch_user_id;
    }

    protected function setMode(int $a_mode) : void
    {
        $this->mode = $a_mode;
    }

    public function getMode() : int
    {
        return $this->mode;
    }

    protected function setTargetRefId(int $a_ref_id) : void
    {
        $this->target_ref_id = $a_ref_id;
    }

    public function getTargetRefId() : int
    {
        return $this->target_ref_id;
    }

    public function setSourceRefId(int $a_val) : void
    {
        $this->root_ref_id = $a_val;
    }

    /**
     */
    public function getsourcerefid() : int
    {
        return $this->root_ref_id;
    }

    /**
     * initialize visible categories
     */
    public function initialize(
        int $a_mode,
        int $a_source_ref_id = 0,
        bool $a_use_cache = false,
        int $a_cat_id = 0
    ) : void {
        if ($this->getMode() != 0) {
            throw new ilCalCategoriesInitializedMultipleException("ilCalendarCategories is initialized multiple times for user " . $this->user_id . ".");
        }

        $this->setMode($a_mode);

        // see comments in https://mantis.ilias.de/view.php?id=25254
        if ($a_use_cache && $this->getMode() != self::MODE_REPOSITORY_CONTAINER_ONLY) {
            // Read categories from cache
            if ($cats = ilCalendarCache::getInstance()->getEntry($this->user_id . ':' . $a_mode . ':categories:' . $a_source_ref_id)) {
                if ($this->getMode() != self::MODE_REPOSITORY &&
                    $this->getMode() != self::MODE_CONSULTATION &&
                    $this->getMode() != self::MODE_PORTFOLIO_CONSULTATION) {
                    $this->wakeup($cats);
                    return;
                }
            }
        }

        switch ($this->getMode()) {
            case self::MODE_REMOTE_ACCESS:
                if (ilCalendarUserSettings::_getInstance()->getCalendarSelectionType() == ilCalendarUserSettings::CAL_SELECTION_MEMBERSHIP) {
                    $this->readPDCalendars();
                } else {
                    $this->readSelectedItemCalendars();
                }
                break;

            case self::MODE_REMOTE_SELECTED:
                $this->readSelectedCalendar($a_source_ref_id);
                break;

            case self::MODE_PERSONAL_DESKTOP_MEMBERSHIP:
                $this->readPDCalendars();
                break;

            case self::MODE_PERSONAL_DESKTOP_ITEMS:
                $this->readSelectedItemCalendars();
                break;

            case self::MODE_REPOSITORY:
                $this->root_ref_id = $a_source_ref_id;
                $this->root_obj_id = ilObject::_lookupObjId($this->root_ref_id);
                $this->readReposCalendars();
                break;

            case self::MODE_REPOSITORY_CONTAINER_ONLY:
                $this->root_ref_id = $a_source_ref_id;
                $this->root_obj_id = ilObject::_lookupObjId($this->root_ref_id);
                $this->readReposCalendars(true);
                break;

            case self::MODE_MANAGE:
                $this->readPDCalendars();
                $this->readSelectedItemCalendars();
                break;

            case self::MODE_CONSULTATION:
                #$this->readPrivateCalendars();
                $this->setTargetRefId($a_source_ref_id);
                $this->readConsultationHoursCalendar($a_source_ref_id);
                break;

            case self::MODE_PORTFOLIO_CONSULTATION:
                $this->readConsultationHoursCalendar();
                break;

            case self::MODE_SINGLE_CALENDAR:
                $this->readSingleCalendar($a_cat_id);
                break;
        }

        if ($a_use_cache) {
            // Store in cache
            ilCalendarCache::getInstance()->storeEntry(
                $this->user_id . ':' . $a_mode . ':categories:' . $a_source_ref_id,
                $this->sleep(),
                $this->user_id,
                $a_mode,
                'categories'
            );
        }
    }

    public function getCategoryInfo(int $a_cat_id) : array
    {
        if (isset($this->categories_info[$a_cat_id])) {
            return $this->categories_info[$a_cat_id];
        }

        if (in_array($a_cat_id, $this->subitem_categories)) {
            foreach ($this->categories as $cat_id) {
                if (in_array($a_cat_id, $this->categories_info[$cat_id]['subitem_ids'])) {
                    return $this->categories_info[$cat_id];
                }
            }
        }
        return [];
    }

    public function getCategoriesInfo() : array
    {
        return $this->categories_info ?: array();
    }

    public function getCategories(bool $a_include_subitem_calendars = false) : array
    {
        if ($a_include_subitem_calendars) {
            return array_merge($this->categories, $this->subitem_categories);
        }

        return $this->categories ?: array();
    }

    public function getSubitemCategories(int $a_cat_id) : array
    {
        if (!isset($this->categories_info[$a_cat_id]['subitem_ids'])) {
            return array($a_cat_id);
        }
        return array_merge((array) $this->categories_info[$a_cat_id]['subitem_ids'], array($a_cat_id));
    }

    public function prepareCategoriesOfUserForSelection() : array
    {
        $has_personal_calendar = false;
        $cats = [];
        foreach ($this->categories_info as $info) {
            if ($info['obj_type'] == 'sess' || $info['obj_type'] == 'exc') {
                continue;
            }
            if ($info['type'] == ilCalendarCategory::TYPE_USR and $info['editable']) {
                $has_personal_calendar = true;
            }

            if ($info['editable']) {
                $cats[$info['cat_id']] = $info['title'];
            }
        }
        // If there
        if (!$has_personal_calendar) {
            $cats[0] = $this->lng->txt('cal_default_calendar');
        }
        return $cats;
    }

    /**
     * Get all calendars that allow send of notifications
     * (Editable and course group calendars)
     */
    public function getNotificationCalendars() : array
    {
        $not = array();
        foreach ($this->categories_info as $info) {
            if ($info['type'] == ilCalendarCategory::TYPE_OBJ and $info['editable']) {
                if (ilObject::_lookupType($info['obj_id']) == 'crs' or ilObject::_lookupType($info['obj_id']) == 'grp') {
                    $not[] = (int) $info['cat_id'];
                }
            }
        }
        return $not;
    }

    /**
     * check if category is editable
     */
    public function isEditable(int $a_cat_id) : bool
    {
        return isset($this->categories_info[$a_cat_id]['editable']) && $this->categories_info[$a_cat_id]['editable'];
    }

    /**
     * check if category is visible
     */
    public function isVisible($a_cat_id) : bool
    {
        return in_array($a_cat_id, $this->categories) || in_array($a_cat_id, $this->subitem_categories);
    }

    /**
     * Read categories of user
     */
    protected function readPDCalendars() : void
    {
        $this->readPublicCalendars();
        $this->readPrivateCalendars();
        $this->readConsultationHoursCalendar();
        $this->readBookingCalendar();

        $this->readSelectedCategories(ilParticipants::_getMembershipByType($this->user_id, ['crs']));
        $this->readSelectedCategories(ilParticipants::_getMembershipByType($this->user_id, ['grp']));

        $this->addSubitemCalendars();
    }

    /**
     * Read info about selected calendar
     */
    protected function readSelectedCalendar(int $a_cal_id) : void
    {
        $cat = new ilCalendarCategory($a_cal_id);
        if ($cat->getType() == ilCalendarCategory::TYPE_OBJ) {
            $this->readSelectedCategories(array($cat->getObjId()));
            $this->addSubitemCalendars();
        } else {
            $this->categories[] = $a_cal_id;
        }
    }

    protected function readSelectedItemCalendars() : void
    {
        $this->readPublicCalendars();
        $this->readPrivateCalendars();
        $this->readConsultationHoursCalendar();
        $this->readBookingCalendar();

        $obj_ids = array();

        $courses = array();
        $groups = array();
        $sessions = array();
        $exercises = array();
        foreach ($this->fav_rep->getFavouritesOfUser(
            $this->user->getId(),
            array('crs', 'grp', 'sess', 'exc')
        ) as $item) {
            if ($this->access->checkAccess('read', '', $item['ref_id'])) {
                switch ($item['type']) {
                    case 'crs':
                        $courses[] = $item['obj_id'];
                        break;

                    case 'sess':
                        $sessions[] = $item['obj_id'];
                        break;

                    case 'grp':
                        $groups[] = $item['obj_id'];
                        break;

                    case 'exc':
                        $exercises[] = $item['obj_id'];
                        break;
                }
            }
        }
        $this->readSelectedCategories($courses);
        $this->readSelectedCategories($sessions);
        $this->readSelectedCategories($groups);
        $this->readSelectedCategories($exercises);

        $this->addSubitemCalendars();
    }

    /**
     * Read available repository calendars
     */
    protected function readReposCalendars($a_container_only = false) : void
    {
        if (!$a_container_only) {
            $this->readPublicCalendars();
            $this->readPrivateCalendars();
            //$this->readConsultationHoursCalendar($this->root_ref_id);
            $this->readAllConsultationHoursCalendarOfContainer($this->root_ref_id);
        }

        #$query = "SELECT ref_id,obd.obj_id obj_id FROM tree t1 ".
        #	"JOIN object_reference obr ON t1.child = obr.ref_id ".
        #	"JOIN object_data obd ON obd.obj_id = obr.obj_id ".
        #	"WHERE t1.lft >= (SELECT lft FROM tree WHERE child = ".$this->db->quote($this->root_ref_id,'integer')." ) ".
        #	"AND t1.lft <= (SELECT rgt FROM tree WHERE child = ".$this->db->quote($this->root_ref_id,'integer')." ) ".
        #	"AND ".$ilDB->in('type',array('crs','grp','sess'),false,'text')." ".
        #	"AND tree = 1";

        // alternative 1: do not aggregate items of current course
        if (false) {        //
            $subtree_query = $GLOBALS['DIC']['tree']->getSubTreeQuery(
                $this->root_ref_id,
                array('object_reference.ref_id', 'object_data.obj_id'),
                array('crs', 'grp', 'sess', 'exc')
            );

            $res = $this->db->query($subtree_query);
            $obj_ids = array();
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                if ($this->tree->isDeleted((int) $row->ref_id)) {
                    continue;
                }

                $obj_type = ilObject::_lookupType((int) $row->obj_id);
                if ($obj_type == 'crs' or $obj_type == 'grp') {
                    //Added for calendar revision --> https://goo.gl/CXGTRF
                    //In 5.2-trunk, the booking pools did not appear in the marginal calendar.
                    $this->readBookingCalendar();
                    // Check for global/local activation
                    if (!ilCalendarSettings::_getInstance()->lookupCalendarActivated((int) $row->obj_id)) {
                        continue;
                    }
                }
                if ($this->access->checkAccess('read', '', (int) $row->ref_id)) {
                    $obj_ids[] = (int) $row->obj_id;
                }
            }
            $this->readSelectedCategories($obj_ids, $this->root_ref_id);
        } else {    // alternative 2: aggregate items of current course (discussion with timon 3.8.3017: this is the current preference)
            $this->readSelectedCategories(array($this->root_obj_id), $this->root_ref_id);
        }

        $this->addSubitemCalendars();

        if (!$a_container_only) {
            $this->readSelectedCategories(ilParticipants::_getMembershipByType($this->user_id, ['crs']));
            $this->readSelectedCategories(ilParticipants::_getMembershipByType($this->user_id, ['grp']));

            $repository = new IliasDBEmployeeTalkSeriesRepository($this->user, $this->db);
            $talks = $repository->findByOwnerAndEmployee();
            $talkIds = array_map(function (ilObjEmployeeTalkSeries $item) {
                return $item->getId();
            }, $talks);
    
            $this->readSelectedCategories($talkIds, 0, false);
        }
    }

    public function readSingleCalendar(int $a_cat_id) : void
    {
        $cat = new ilCalendarCategory($a_cat_id);
        switch ($cat->getType()) {
            case ilCalendarCategory::TYPE_OBJ:
                $this->readSelectedCalendar($a_cat_id);
                break;

            case ilCalendarCategory::TYPE_GLOBAL:
                $this->readPublicCalendars(array($a_cat_id));
                break;

            case ilCalendarCategory::TYPE_USR:
                $this->readPrivateCalendars(array($a_cat_id));
                break;

            case ilCalendarCategory::TYPE_CH:
                $this->readConsultationHoursCalendar($this->root_ref_id, $a_cat_id);
                break;

            case ilCalendarCategory::TYPE_BOOK:
                $this->readBookingCalendar();
                break;
        }
    }

    /**
     * Read public calendars
     */
    protected function readPublicCalendars($cat_ids = null) : void
    {
        $in = "";
        if (is_array($cat_ids)) {
            $in = " AND " . $this->db->in('cat_id', $cat_ids, false, 'integer') . " ";
        }

        // global categories
        $query = "SELECT * FROM cal_categories " .
            "WHERE type = " . $this->db->quote(ilCalendarCategory::TYPE_GLOBAL, 'integer') . " " . $in .
            "ORDER BY title ";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->categories[] = (int) $row->cat_id;
            $this->categories_info[(int) $row->cat_id]['obj_type'] = '';
            $this->categories_info[(int) $row->cat_id]['source_ref_id'] = 0;
            $this->categories_info[(int) $row->cat_id]['obj_id'] = (int) $row->obj_id;
            $this->categories_info[(int) $row->cat_id]['cat_id'] = (int) $row->cat_id;
            $this->categories_info[(int) $row->cat_id]['title'] = $row->title;
            $this->categories_info[(int) $row->cat_id]['color'] = $row->color;
            $this->categories_info[(int) $row->cat_id]['type'] = (int) $row->type;
            $this->categories_info[(int) $row->cat_id]['editable'] = $this->rbacsystem->checkAccess(
                'edit_event',
                ilCalendarSettings::_getInstance()->getCalendarSettingsId()
            );
            $this->categories_info[(int) $row->cat_id]['settings'] = $this->rbacsystem->checkAccess(
                'write',
                ilCalendarSettings::_getInstance()->getCalendarSettingsId()
            );
            $this->categories_info[(int) $row->cat_id]['accepted'] = false;
            $this->categories_info[(int) $row->cat_id]['remote'] = (int) $row->loc_type == ilCalendarCategory::LTYPE_REMOTE;
        }
    }

    /**
     * Read private calendars
     */
    protected function readPrivateCalendars(?array $only_cat_ids = null) : void
    {
        $cat_ids = [];
        $in = "";
        if (is_array($only_cat_ids)) {
            $in = " AND " . $this->db->in('cat_id', $only_cat_ids, false, 'integer') . " ";
        }

        // First read private calendars of user
        $query = "SELECT cat_id FROM cal_categories " .
            "WHERE type = " . $this->db->quote(ilCalendarCategory::TYPE_USR, 'integer') . " " .
            "AND obj_id = " . $this->db->quote($this->user->getId(), 'integer') . " " . $in;
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $cat_ids[] = (int) $row->cat_id;
        }

        // Read shared calendars
        $accepted_ids = ilCalendarSharedStatus::getAcceptedCalendars($this->user->getId());
        if (!$cat_ids = array_merge($cat_ids, $accepted_ids)) {
            return;
        }
        if (is_array($only_cat_ids)) {
            $cat_ids = array_filter($cat_ids, function ($id) use ($only_cat_ids) {
                return in_array($id, $only_cat_ids);
            });
        }
        // user categories
        $query = "SELECT * FROM cal_categories " .
            "WHERE type = " . $this->db->quote(ilCalendarCategory::TYPE_USR, 'integer') . " " .
            "AND " . $this->db->in('cat_id', $cat_ids, false, 'integer') . " " .
            "ORDER BY title ";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->categories[] = (int) $row->cat_id;
            $this->categories_info[(int) $row->cat_id]['obj_type'] = '';
            $this->categories_info[(int) $row->cat_id]['source_ref_id'] = 0;
            $this->categories_info[(int) $row->cat_id]['obj_id'] = (int) $row->obj_id;
            $this->categories_info[(int) $row->cat_id]['cat_id'] = (int) $row->cat_id;
            $this->categories_info[(int) $row->cat_id]['title'] = $row->title;
            $this->categories_info[(int) $row->cat_id]['color'] = $row->color;
            $this->categories_info[(int) $row->cat_id]['type'] = (int) $row->type;

            if (in_array((int) $row->cat_id, $accepted_ids)) {
                $shared = new ilCalendarShared((int) $row->cat_id);
                if ($shared->isEditableForUser($this->user->getId())) {
                    $this->categories_info[(int) $row->cat_id]['editable'] = true;
                } else {
                    $this->categories_info[(int) $row->cat_id]['editable'] = false;
                }
            } else {
                $this->categories_info[(int) $row->cat_id]['editable'] = true;
            }
            if ($this->user->getId() == (int) $row->obj_id) {
                $this->categories_info[(int) $row->cat_id]['settings'] = true;
            } else {
                $this->categories_info[(int) $row->cat_id]['settings'] = false;
            }

            $this->categories_info[(int) $row->cat_id]['accepted'] = in_array((int) $row->cat_id, $accepted_ids);
            $this->categories_info[(int) $row->cat_id]['remote'] = ((int) $row->loc_type == ilCalendarCategory::LTYPE_REMOTE);
        }
    }

    /**
     * Read personal consultation hours calendar of all tutors for a container
     */
    public function readAllConsultationHoursCalendarOfContainer(int $a_container_ref_id) : void
    {
        $obj_id = ilObject::_lookupObjId($a_container_ref_id);
        $participants = ilCourseParticipants::_getInstanceByObjId($obj_id);
        $users = array_unique(array_merge($participants->getTutors(), $participants->getAdmins()));
        $users = ilBookingEntry::lookupBookableUsersForObject([$obj_id], $users);
        $old_ch = $this->getCHUserId();
        foreach ($users as $user) {
            $this->setCHUserId($user);
            $this->readConsultationHoursCalendar($a_container_ref_id);
        }
        $this->setCHUserId($old_ch);
    }

    /**
     * Read personal consultation hours calendar
     */
    public function readConsultationHoursCalendar(?int $a_target_ref_id = null, int $a_cat_id = 0) : void
    {
        if (!$this->getCHUserId()) {
            $this->setCHUserId($this->user_id);
        }

        if ($a_target_ref_id) {
            $target_obj_id = ilObject::_lookupObjId($a_target_ref_id);

            $query = 'SELECT DISTINCT(cc.cat_id) FROM booking_entry be ' .
                'LEFT JOIN booking_obj_assignment bo ON be.booking_id = bo.booking_id ' .
                'JOIN cal_entries ce ON be.booking_id = ce.context_id ' .
                'JOIN cal_cat_assignments ca ON ce.cal_id = ca.cal_id ' .
                'JOIN cal_categories cc ON ca.cat_id = cc.cat_id ' .
                'WHERE ((bo.target_obj_id IS NULL) OR bo.target_obj_id = ' . $this->db->quote(
                    $target_obj_id,
                    'integer'
                ) . ' ) ';

            // limit only to user if no cat id is given
            if ($a_cat_id == 0) {
                $query .= 'AND cc.obj_id = ' . $this->db->quote($this->getCHUserId(), 'integer');
            }

            $res = $this->db->query($query);
            $categories = array();
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                if ($a_cat_id == 0 || (int) $row->cat_id == $a_cat_id) {
                    $categories[] = (int) $row->cat_id;
                }
            }

            if ($categories) {
                $query = 'SELECT * FROM cal_categories ' .
                    'WHERE ' . $this->db->in('cat_id', $categories, false, 'integer');
                $res = $this->db->query($query);
                while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                    $this->categories[] = (int) $row->cat_id;
                    $this->categories_info[(int) $row->cat_id]['obj_type'] = '';
                    $this->categories_info[(int) $row->cat_id]['source_ref_id'] = 0;
                    $this->categories_info[(int) $row->cat_id]['obj_id'] = (int) $row->obj_id;
                    $this->categories_info[(int) $row->cat_id]['cat_id'] = (int) $row->cat_id;
                    $this->categories_info[(int) $row->cat_id]['title'] = ilObjUser::_lookupFullname((int) $row->obj_id);
                    $this->categories_info[(int) $row->cat_id]['color'] = $row->color;
                    $this->categories_info[(int) $row->cat_id]['type'] = (int) $row->type;
                    $this->categories_info[(int) $row->cat_id]['editable'] = false;
                    $this->categories_info[(int) $row->cat_id]['settings'] = false;
                    $this->categories_info[(int) $row->cat_id]['accepted'] = false;
                    $this->categories_info[(int) $row->cat_id]['remote'] = false;
                }
            }
        } else { // no category given
            $filter = ($a_cat_id > 0)
                ? " AND cat_id = " . $this->db->quote($a_cat_id, "integer")
                : " AND obj_id = " . $this->db->quote($this->getCHUserId(), 'integer');

            $query = "SELECT *  FROM cal_categories cc " .
                "WHERE type = " . $this->db->quote(ilCalendarCategory::TYPE_CH, 'integer') . ' ' . $filter;
            $res = $this->db->query($query);
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->categories[] = (int) $row->cat_id;
                $this->categories_info[(int) $row->cat_id]['obj_type'] = '';
                $this->categories_info[(int) $row->cat_id]['source_ref_id'] = 0;
                $this->categories_info[(int) $row->cat_id]['obj_id'] = (int) $row->obj_id;
                $this->categories_info[(int) $row->cat_id]['cat_id'] = (int) $row->cat_id;
                $this->categories_info[(int) $row->cat_id]['title'] = $row->title;
                $this->categories_info[(int) $row->cat_id]['color'] = $row->color;
                $this->categories_info[(int) $row->cat_id]['type'] = (int) $row->type;
                $this->categories_info[(int) $row->cat_id]['editable'] = false;
                $this->categories_info[(int) $row->cat_id]['settings'] = false;
                $this->categories_info[(int) $row->cat_id]['accepted'] = false;
                $this->categories_info[(int) $row->cat_id]['remote'] = false;
            }
        }
    }

    /**
     * Read booking manager calendar
     */
    public function readBookingCalendar(?int $user_id = null) : void
    {
        if (!$user_id) {
            $user_id = $this->user_id;
        }

        $query = "SELECT *  FROM cal_categories " .
            "WHERE type = " . $this->db->quote(ilCalendarCategory::TYPE_BOOK, 'integer') . ' ' .
            "AND obj_id = " . $this->db->quote($user_id, 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->categories[] = (int) $row->cat_id;
            $this->categories_info[(int) $row->cat_id]['source_ref_id'] = 0;
            $this->categories_info[(int) $row->cat_id]['obj_type'] = '';
            $this->categories_info[(int) $row->cat_id]['obj_id'] = (int) $row->obj_id;
            $this->categories_info[(int) $row->cat_id]['cat_id'] = (int) $row->cat_id;
            $this->categories_info[(int) $row->cat_id]['title'] = $row->title;
            $this->categories_info[(int) $row->cat_id]['color'] = $row->color;
            $this->categories_info[(int) $row->cat_id]['type'] = (int) $row->type;
            $this->categories_info[(int) $row->cat_id]['editable'] = false;
            $this->categories_info[(int) $row->cat_id]['settings'] = false;
            $this->categories_info[(int) $row->cat_id]['accepted'] = false;
            $this->categories_info[(int) $row->cat_id]['remote'] = false;
        }
    }

    protected function readSelectedCategories(array $a_obj_ids, int $a_source_ref_id = 0, bool $check_permissions = true) : void
    {
        if (!count($a_obj_ids)) {
            return;
        }

        $query = "SELECT * FROM cal_categories " .
            "WHERE type = " . $this->db->quote(ilCalendarCategory::TYPE_OBJ, 'integer') . " " .
            "AND " . $this->db->in('obj_id', $a_obj_ids, false, 'integer') . " " .
            "ORDER BY title ";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            // check activation/deactivation
            $obj_type = ilObject::_lookupType((int) $row->obj_id);
            if ($obj_type == 'crs' or $obj_type == 'grp') {
                if (!ilCalendarSettings::_getInstance()->lookupCalendarActivated((int) $row->obj_id)) {
                    continue;
                }
            }

            $editable = false;
            $exists = false;
            $settings = false;
            foreach (ilObject::_getAllReferences((int) $row->obj_id) as $ref_id) {
                if ($this->access->checkAccess('edit_event', '', $ref_id)) {
                    $settings = true;
                }
                if ($this->access->checkAccess('edit_event', '', $ref_id)) {
                    $exists = true;
                    $editable = true;
                    break;
                } elseif ($this->access->checkAccess('read', '', $ref_id)) {
                    $exists = true;
                }
            }
            if (!$exists && $check_permissions) {
                continue;
            }
            $this->categories_info[(int) $row->cat_id]['editable'] = $editable;
            $this->categories_info[(int) $row->cat_id]['settings'] = $settings;

            $this->categories[] = (int) $row->cat_id;
            $this->categories_info[(int) $row->cat_id]['obj_type'] = ilObject::_lookupType((int) $row->obj_id);
            $this->categories_info[(int) $row->cat_id]['obj_id'] = (int) $row->obj_id;
            $this->categories_info[(int) $row->cat_id]['cat_id'] = (int) $row->cat_id;
            $this->categories_info[(int) $row->cat_id]['color'] = $row->color;
            #$this->categories_info[$row->cat_id]['title'] = ilObject::_lookupTitle($row->obj_id);
            $this->categories_info[(int) $row->cat_id]['title'] = $row->title;
            $this->categories_info[(int) $row->cat_id]['type'] = (int) $row->type;
            $this->categories_info[(int) $row->cat_id]['remote'] = false;
            $this->categories_info[(int) $row->cat_id]['source_ref_id'] = $a_source_ref_id;
        }
    }

    /**
     * Add subitem calendars
     * E.g. session calendars in courses, groups
     */
    protected function addSubitemCalendars() : void
    {
        $course_ids = array();
        foreach ($this->categories as $cat_id) {
            if (isset($this->categories_info[$cat_id]['obj_type']) &&
                in_array($this->categories_info[$cat_id]['obj_type'], ['crs', 'grp', 'tals'])) {
                $course_ids[] = $this->categories_info[$cat_id]['obj_id'];
            }
        }

        $query = "SELECT od2.obj_id sess_id, od1.obj_id crs_id,cat_id, or2.ref_id sess_ref_id, od2.type FROM object_data od1 " .
            "JOIN object_reference or1 ON od1.obj_id = or1.obj_id " .
            "JOIN tree t ON or1.ref_id = t.parent " .
            "JOIN object_reference or2 ON t.child = or2.ref_id " .
            "JOIN object_data od2 ON or2.obj_id = od2.obj_id " .
            "JOIN cal_categories cc ON od2.obj_id = cc.obj_id " .
            "WHERE " . $this->db->in('od2.type', array('sess', 'exc', 'etal'), false, 'text') .
            "AND (od1.type = 'crs' OR od1.type = 'grp' OR od1.type = 'tals') " .
            "AND " . $this->db->in('od1.obj_id', $course_ids, false, 'integer') . ' ' .
            "AND or2.deleted IS NULL";

        $res = $this->db->query($query);
        $cat_ids = array();
        $course_sessions = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($row->type !== 'etal') {
                if (
                    !$this->access->checkAccessOfUser($this->user_id, 'read', '', (int) $row->sess_ref_id) ||
                    !$this->access->checkAccessOfUser($this->user_id, 'visible', '', (int) $row->sess_ref_id)
                ) {
                    continue;
                }
            }
            $cat_ids[] = (int) $row->cat_id;
            $course_sessions[(int) $row->crs_id][(int) $row->sess_id] = (int) $row->cat_id;
            $this->subitem_categories[] = (int) $row->cat_id;
        }

        foreach ($this->categories as $cat_id) {
            if (
                (isset($this->categories_info[$cat_id]['obj_type']) &&
                    in_array($this->categories_info[$cat_id]['obj_type'], ['crs', 'grp', 'tals'])) &&
                isset($this->categories_info[$cat_id]['obj_id']) &&
                isset($course_sessions[$this->categories_info[$cat_id]['obj_id']]) &&
                is_array($course_sessions[$this->categories_info[$cat_id]['obj_id']])) {
                foreach ($course_sessions[$this->categories_info[$cat_id]['obj_id']] as $sess_id => $sess_cat_id) {
                    $this->categories_info[$cat_id]['subitem_ids'][$sess_id] = $sess_cat_id;
                    $this->categories_info[$cat_id]['subitem_obj_ids'][$sess_cat_id] = $sess_id;
                }
            } else {
                $this->categories_info[$cat_id]['subitem_ids'] = array();
                $this->categories_info[$cat_id]['subitem_obj_ids'] = array();
            }
        }
    }

    /**
     * Lookup private categories of user
     * @return array[]
     */
    public static function lookupPrivateCategories(int $a_user_id) : array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // First read private calendars of user
        $set = $ilDB->query("SELECT * FROM cal_categories " .
            "WHERE type = " . $ilDB->quote(ilCalendarCategory::TYPE_USR, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_user_id, 'integer'));
        $cats = array();

        while ($rec = $ilDB->fetchAssoc($set)) {
            $rec['cat_id'] = (int) $rec['cat_id'];
            $rec['obj_id'] = (int) $rec['obj_id'];
            $rec['type'] = (int) $rec['type'];
            $rec['loc_type'] = (int) $rec['loc_type'];
            $cats[] = $rec;
        }
        return $cats;
    }
}
