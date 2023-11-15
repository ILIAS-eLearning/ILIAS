<?php

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

declare(strict_types=1);

/**
 * Stores calendar categories
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @ingroup ServicesCalendar
 */
class ilCalendarCategory
{
    public const LTYPE_LOCAL = 1;
    public const LTYPE_REMOTE = 2;

    private static ?array $instances = [];

    public const DEFAULT_COLOR = '#04427e';

    public const TYPE_UNDEFINED = 0;
    public const TYPE_USR = 1;        // user
    public const TYPE_OBJ = 2;        // object
    public const TYPE_GLOBAL = 3;    // global
    public const TYPE_CH = 4;        // consultation hours
    public const TYPE_BOOK = 5;    // booking manager

    protected static array $SORTED_TYPES = array(
        0 => self::TYPE_GLOBAL,
        1 => self::TYPE_USR,
        2 => self::TYPE_CH,
        3 => self::TYPE_BOOK,
        4 => self::TYPE_OBJ
    );

    protected int $cat_id = 0;
    protected string $color = '';
    protected int $type = self::TYPE_USR;
    protected int $obj_id = 0;
    protected string $obj_type = '';
    protected string $title = '';

    protected int $location = self::LTYPE_LOCAL;
    protected string $remote_url = '';
    protected string $remote_user = '';
    protected string $remote_pass = '';
    protected ?ilDateTime $remote_sync = null;

    protected ilDBInterface $db;

    public function __construct(int $a_cat_id = 0)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->cat_id = $a_cat_id;

        $this->read();
    }

    /**
     * get instance by obj_id
     */
    public static function _getInstanceByObjId(int $a_obj_id): ?ilCalendarCategory
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT cat_id FROM cal_categories " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND type = " . $ilDB->quote(self::TYPE_OBJ, 'integer');
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return new ilCalendarCategory((int) $row->cat_id);
        }
        return null;
    }

    public static function getInstanceByCategoryId(int $a_cat_id): ilCalendarCategory
    {
        if (!isset(self::$instances[$a_cat_id])) {
            return self::$instances[$a_cat_id] = new ilCalendarCategory($a_cat_id);
        }
        return self::$instances[$a_cat_id];
    }

    /**
     * Lookup sort index of calendar type
     */
    public static function lookupCategorySortIndex(int $a_type_id): int
    {
        return (int) array_search($a_type_id, self::$SORTED_TYPES);
    }

    /**
     * get all assigned appointment ids
     * @return int[]
     */
    public static function lookupAppointments(int $a_category_id): array
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $query = "SELECT * FROM cal_cat_assignments " .
            'WHERE cat_id = ' . $ilDB->quote($a_category_id, 'integer');
        $res = $ilDB->query($query);
        $apps = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $apps[] = (int) $row->cal_id;
        }
        return $apps;
    }

    public function getCategoryID(): int
    {
        return $this->cat_id;
    }

    public function setTitle(string $a_title): void
    {
        $this->title = $a_title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setColor(string $a_color): void
    {
        $this->color = $a_color;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setType(int $a_type): void
    {
        $this->type = $a_type;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setObjId(int $a_obj_id): void
    {
        $this->obj_id = $a_obj_id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    public function getObjType(): string
    {
        return $this->obj_type;
    }

    public function getLocationType(): int
    {
        return $this->location;
    }

    public function setLocationType(int $a_type): void
    {
        $this->location = $a_type;
    }

    public function setRemoteUrl(string $a_url): void
    {
        $this->remote_url = $a_url;
    }

    public function getRemoteUrl(): string
    {
        return $this->remote_url;
    }

    public function setRemoteUser(string $a_user): void
    {
        $this->remote_user = $a_user;
    }

    public function getRemoteUser(): string
    {
        return $this->remote_user;
    }

    public function setRemotePass(string $a_pass): void
    {
        $this->remote_pass = $a_pass;
    }

    public function getRemotePass(): string
    {
        return $this->remote_pass;
    }

    /**
     * Set remote sync last execution
     */
    public function setRemoteSyncLastExecution(ilDateTime $dt): void
    {
        $this->remote_sync = $dt;
    }

    /**
     * Get last execution date of remote sync
     */
    public function getRemoteSyncLastExecution(): ilDateTime
    {
        if ($this->remote_sync instanceof ilDateTime) {
            return $this->remote_sync;
        }
        return new ilDateTime();
    }

    public function add(): int
    {
        $next_id = $this->db->nextId('cal_categories');
        $query = "INSERT INTO cal_categories (cat_id,obj_id,color,type,title,loc_type,remote_url,remote_user,remote_pass,remote_sync) " .
            "VALUES ( " .
            $this->db->quote($next_id, 'integer') . ", " .
            $this->db->quote($this->getObjId(), 'integer') . ", " .
            $this->db->quote($this->getColor(), 'text') . ", " .
            $this->db->quote($this->getType(), 'integer') . ", " .
            /*
             * The title needs to be truncated to fit into the table column. This is a pretty
             * brute force method for doing so, but right now I can't find a better place for it.
             */
            $this->db->quote(substr($this->getTitle(), 0, 128), 'text') . ", " .
            $this->db->quote($this->getLocationType(), 'integer') . ', ' .
            $this->db->quote($this->getRemoteUrl(), 'text') . ', ' .
            $this->db->quote($this->getRemoteUser(), 'text') . ', ' .
            $this->db->quote($this->getRemotePass(), 'text') . ', ' .
            $this->db->quote(
                $this->getRemoteSyncLastExecution()->get(IL_CAL_DATETIME, '', ilTimeZone::UTC),
                'timestamp'
            ) . ' ' .
            ")";

        $this->db->manipulate($query);

        $this->cat_id = $next_id;
        return $this->cat_id;
    }

    public function update(): void
    {
        $query = "UPDATE cal_categories " .
            "SET obj_id = " . $this->db->quote($this->getObjId(), 'integer') . ", " .
            "color = " . $this->db->quote($this->getColor(), 'text') . ", " .
            "type = " . $this->db->quote($this->getType(), 'integer') . ", " .
            /*
             * The title needs to be truncated to fit into the table column. This is a pretty
             * brute force method for doing so, but right now I can't find a better place for it.
             */
            "title = " . $this->db->quote(substr($this->getTitle(), 0, 128), 'text') . ", " .
            "loc_type = " . $this->db->quote($this->getLocationType(), 'integer') . ', ' .
            "remote_url = " . $this->db->quote($this->getRemoteUrl(), 'text') . ', ' .
            "remote_user = " . $this->db->quote($this->getRemoteUser(), 'text') . ', ' .
            "remote_pass = " . $this->db->quote($this->getRemotePass(), 'text') . ', ' .
            'remote_sync = ' . $this->db->quote($this->getRemoteSyncLastExecution()->get(
                IL_CAL_DATETIME,
                '',
                ilTimeZone::UTC
            ), 'timestamp') . ' ' .
            "WHERE cat_id = " . $this->db->quote($this->cat_id, 'integer') . " ";
        $res = $this->db->manipulate($query);
    }

    public function delete(): void
    {
        $query = "DELETE FROM cal_categories " .
            "WHERE cat_id = " . $this->db->quote($this->cat_id, 'integer') . " ";
        $res = $this->db->manipulate($query);

        ilCalendarVisibility::_deleteCategories($this->cat_id);

        foreach (ilCalendarCategoryAssignments::_getAssignedAppointments(array($this->cat_id)) as $app_id) {
            ilCalendarEntry::_delete($app_id);
        }
        ilCalendarCategoryAssignments::_deleteByCategoryId($this->cat_id);
    }

    public function validate(): bool
    {
        if ($this->getLocationType() == ilCalendarCategory::LTYPE_REMOTE && !$this->getRemoteUrl()) {
            return false;
        }
        if (strlen($this->getTitle()) && strlen($this->getColor()) && $this->getType()) {
            return true;
        }
        return false;
    }

    private function read(): void
    {
        if (!$this->cat_id) {
            return;
        }

        $query = "SELECT * FROM cal_categories " .
            "WHERE cat_id = " . $this->db->quote($this->getCategoryID(), 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->cat_id = (int) $row->cat_id;
            $this->obj_id = (int) $row->obj_id;
            $this->type = (int) $row->type;
            $this->color = (string) $row->color;
            $this->title = (string) $row->title;
            $this->location = (int) $row->loc_type;
            $this->remote_url = (string) $row->remote_url;
            $this->remote_user = (string) $row->remote_user;
            $this->remote_pass = (string) $row->remote_pass;

            if ($row->remote_sync) {
                $this->remote_sync = new ilDateTime((string) $row->remote_sync, IL_CAL_DATETIME, 'UTC');
            } else {
                $this->remote_sync = new ilDateTime();
            }
        }
        if ($this->getType() == self::TYPE_OBJ) {
            $this->title = ilObject::_lookupTitle($this->getObjId());
            $this->obj_type = ilObject::_lookupType($this->getObjId());
        }
    }
}
