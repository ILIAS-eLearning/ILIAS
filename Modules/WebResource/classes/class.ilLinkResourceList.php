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
 
/**
 * Class ilLinkResourceList
 * @author Thomas Famula <famula@leifos.com>
 */
class ilLinkResourceList
{
    protected int $webr_id = 0;
    protected string $title = '';
    protected string $description = '';
    protected int $c_date = 0;
    protected int $m_date = 0;

    protected ilDBInterface $db;

    public function __construct(int $webr_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->webr_id = $webr_id;
        $this->read();
    }

    public function setListResourceId(int $a_id) : void
    {
        $this->webr_id = $a_id;
    }

    public function getListResourceId() : int
    {
        return $this->webr_id;
    }

    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setDescription(string $a_description) : void
    {
        $this->description = $a_description;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    protected function setCreateDate(int $a_date) : void
    {
        $this->c_date = $a_date;
    }

    public function getCreateDate() : int
    {
        return $this->c_date;
    }

    protected function setLastUpdateDate(int $a_date) : void
    {
        $this->m_date = $a_date;
    }

    public function getLastUpdateDate() : int
    {
        return $this->m_date;
    }

    public function read() : bool
    {
        $query = "SELECT * FROM webr_lists " .
            "WHERE webr_id = " . $this->db->quote(
                $this->getListResourceId(),
                'integer'
            );

        $res = $this->db->query($query);
        if ($this->db->numRows($res) !== 0) {
            while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
                $this->setTitle((string) $row->title);
                $this->setDescription((string) $row->description);
                $this->setCreateDate((int) $row->create_date);
                $this->setLastUpdateDate((int) $row->last_update);
            }
            return true;
        }
        return false;
    }

    public function delete(bool $a_update_history = true) : bool
    {
        $query = "DELETE FROM webr_lists " .
            "WHERE webr_id = " . $this->db->quote(
                $this->getListResourceId(),
                'integer'
            );
        $res = $this->db->manipulate($query);

        if ($a_update_history) {
            ilHistory::_createEntry(
                $this->getListResourceId(),
                "delete",
                [$this->getTitle()]
            );
        }
        return true;
    }

    public function update(bool $a_update_history = true) : bool
    {
        if (!$this->getListResourceId()) {
            return false;
        }
        $this->setLastUpdateDate(time());
        $query = "UPDATE webr_lists " .
            "SET title = " . $this->db->quote(
                $this->getTitle(),
                'text'
            ) . ", " .
            "description = " . $this->db->quote(
                $this->getDescription(),
                'text'
            ) . ", " .
            "last_update = " . $this->db->quote(
                $this->getLastUpdateDate(),
                'integer'
            ) . " " .
            "WHERE webr_id = " . $this->db->quote(
                $this->getListResourceId(),
                'integer'
            );
        $res = $this->db->manipulate($query);

        if ($a_update_history) {
            ilHistory::_createEntry(
                $this->getListResourceId(),
                "update",
                [$this->getTitle()]
            );
        }
        return true;
    }

    public function add(bool $a_update_history = true) : bool
    {
        $now = time();
        $this->setCreateDate($now);
        $this->setLastUpdateDate($now);

        $query = "INSERT INTO webr_lists (title,description,last_update,create_date,webr_id) " .
            "VALUES( " .
            $this->db->quote($this->getTitle(), 'text') . ", " .
            $this->db->quote($this->getDescription(), 'text') . ", " .
            $this->db->quote($this->getLastUpdateDate(), 'integer') . ", " .
            $this->db->quote($this->getCreateDate(), 'integer') . ", " .
            $this->db->quote($this->getListResourceId(), 'integer') . " " .
            ")";
        $res = $this->db->manipulate($query);

        if ($a_update_history) {
            ilHistory::_createEntry(
                $this->getListResourceId(),
                "add",
                [$this->getTitle()]
            );
        }
        return true;
    }

    public static function lookupList(int $a_webr_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM webr_lists " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer');

        $res = $ilDB->query($query);
        $list = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $list['title'] = (string) $row->title;
            $list['description'] = (string) $row->description;
            $list['create_date'] = (int) $row->create_date;
            $list['last_update'] = (int) $row->last_update;
            $list['webr_id'] = (int) $row->webr_id;
        }
        return $list;
    }

    public static function lookupAllLists() : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM webr_lists";
        $res = $ilDB->query($query);
        $lists = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $lists[$row->webr_id]['title'] = (string) $row->title;
            $lists[$row->webr_id]['description'] = (string) $row->description;
            $lists[$row->webr_id]['create_date'] = (int) $row->create_date;
            $lists[$row->webr_id]['last_update'] = (int) $row->last_update;
            $lists[$row->webr_id]['webr_id'] = (int) $row->webr_id;
        }
        return $lists;
    }

    /**
     * Check if a weblink list was already created or transformed from a single weblink
     */
    public static function checkListStatus(int $a_webr_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT * FROM webr_lists " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer');

        $res = $ilDB->query($query);
        return (bool) $ilDB->numRows($res);
    }
}
