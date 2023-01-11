<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilLinkResourceList
 *
 * @author Thomas Famula <famula@leifos.com>
 */
class ilLinkResourceList
{
    /**
     * @var int
     */
    protected $webr_id;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var int
     */
    protected $c_date;

    /**
     * @var int
     */
    protected $m_date;

    /**
     * ilLinkResourceList constructor.
     * @param int $webr_id
     */
    public function __construct(int $webr_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->webr_id = $webr_id;

        $this->read();
    }

    /**
     * @param int $a_id
     */
    public function setListResourceId(int $a_id)
    {
        $this->webr_id = $a_id;
    }

    /**
     * @return int
     */
    public function getListResourceId()
    {
        return $this->webr_id;
    }

    /**
     * @param string $a_title
     */
    public function setTitle(string $a_title)
    {
        $this->title = $a_title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $a_description
     */
    public function setDescription(string $a_description)
    {
        $this->description = $a_description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param int $a_date
     */
    protected function setCreateDate(int $a_date)
    {
        $this->c_date = $a_date;
    }

    /**
     * @return int
     */
    public function getCreateDate()
    {
        return $this->c_date;
    }

    /**
     * @param int $a_date
     */
    protected function setLastUpdateDate(int $a_date)
    {
        $this->m_date = $a_date;
    }

    /**
     * @return int
     */
    public function getLastUpdateDate()
    {
        return $this->m_date;
    }


    /**
     * @return bool
     */
    public function read()
    {
        $ilDB = $this->db;

        $query = "SELECT * FROM webr_lists " .
            "WHERE webr_id = " . $ilDB->quote($this->getListResourceId(), 'integer');

        $res = $ilDB->query($query);
        if ($ilDB->numRows($res)) {
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

    /**
     * @param bool $a_update_history
     * @return bool
     */
    public function delete(bool $a_update_history = true)
    {
        $ilDB = $this->db;

        $query = "DELETE FROM webr_lists " .
            "WHERE webr_id = " . $ilDB->quote($this->getListResourceId(), 'integer');
        $res = $ilDB->manipulate($query);

        if ($a_update_history) {
            ilHistory::_createEntry(
                $this->getListResourceId(),
                "delete",
                $this->getTitle()
            );
        }

        return true;
    }

    /**
     * @param bool $a_update_history
     * @return bool
     */
    public function update($a_update_history = true)
    {
        $ilDB = $this->db;

        if (!$this->getListResourceId()) {
            return false;
        }

        $this->setLastUpdateDate(time());
        $query = "UPDATE webr_lists " .
            "SET title = " . $ilDB->quote($this->getTitle(), 'text') . ", " .
            "description = " . $ilDB->quote($this->getDescription(), 'text') . ", " .
            "last_update = " . $ilDB->quote($this->getLastUpdateDate(), 'integer') . " " .
            "WHERE webr_id = " . $ilDB->quote($this->getListResourceId(), 'integer');
        $res = $ilDB->manipulate($query);

        if ($a_update_history) {
            ilHistory::_createEntry(
                $this->getListResourceId(),
                "update",
                $this->getTitle()
            );
        }

        return true;
    }

    /**
     * @param bool $a_update_history
     * @return bool
     */
    public function add($a_update_history = true)
    {
        $ilDB = $this->db;

        $now = time();
        $this->setCreateDate($now);
        $this->setLastUpdateDate($now);

        $query = "INSERT INTO webr_lists (title,description,last_update,create_date,webr_id) " .
            "VALUES( " .
            $ilDB->quote($this->getTitle(), 'text') . ", " .
            $ilDB->quote($this->getDescription(), 'text') . ", " .
            $ilDB->quote($this->getLastUpdateDate(), 'integer') . ", " .
            $ilDB->quote($this->getCreateDate(), 'integer') . ", " .
            $ilDB->quote($this->getListResourceId(), 'integer') . " " .
            ")";
        $res = $ilDB->manipulate($query);

        if ($a_update_history) {
            ilHistory::_createEntry(
                $this->getListResourceId(),
                "add",
                $this->getTitle()
            );
        }

        return true;
    }

    /**
     * @param int $a_webr_id
     * @return array
     */
    public static function lookupList(int $a_webr_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM webr_lists " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer');

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $list['title'] = $row->title;
            $list['description'] = $row->description;
            $list['create_date'] = $row->create_date;
            $list['last_update'] = $row->last_update;
            $list['webr_id'] = $row->webr_id;
        }
        return $list ? $list : array();
    }

    /**
     * @return array
     */
    public static function lookupAllLists()
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM webr_lists";

        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $lists[$row->webr_id]['title'] = $row->title;
            $lists[$row->webr_id]['description'] = $row->description;
            $lists[$row->webr_id]['create_date'] = $row->create_date;
            $lists[$row->webr_id]['last_update'] = $row->last_update;
            $lists[$row->webr_id]['webr_id'] = $row->webr_id;
        }
        return $lists ? $lists : array();
    }

    /**
     * Check if a weblink list was already created or transformed from a single weblink
     * @return bool
     */
    public static function checkListStatus(int $a_webr_id)
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM webr_lists " .
            "WHERE webr_id = " . $ilDB->quote($a_webr_id, 'integer');

        $res = $ilDB->query($query);
        if ($ilDB->numRows($res)) {
            return true;
        }
        return false;
    }
}
