<?php

/**
 * Class ilOrgUnitTypeTranslation
 * This class represents a translation for a given ilOrgUnit object and language.
 *
 * @author: Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilOrgUnitTypeTranslation
{
    const TABLE_NAME = 'orgu_types_trans';

    /**
     * @var int
     */
    protected $orgu_type_id;

    /**
     * @var string
     */
    protected $lang = '';

    /**
     * @var array
     */
    protected $members = array();

    /**
     * @var array
     */
    protected $changes = array();

    /**
     * @var array
     */
    protected $members_new = array();

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilLog
     */
    protected $log;

    /**
     * @var array
     */
    protected static $instances = array();


    public function __construct($a_org_type_id=0, $a_lang_code='')
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        $ilLog = $DIC['ilLog'];
        $this->db = $ilDB;
        $this->log = $ilLog;
        if ($a_org_type_id && $a_lang_code) {
            $this->orgu_type_id = (int) $a_org_type_id;
            $this->lang = $a_lang_code;
            $this->read();
        }
    }


    /**
     * Public
     */


    /**
     * Get instance of an ilOrgUnitType object
     * Returns object from cache or from database, returns null if no object was found
     *
     * @param int $a_orgu_type_id ID of an ilOrgUnitType object
     * @param string $a_lang_code Language code
     * @return ilOrgUnitTypeTranslation|null
     */
    public static function getInstance($a_orgu_type_id, $a_lang_code)
    {
        if (!$a_orgu_type_id || !$a_lang_code) {
            return null;
        }
        $cache_id = $a_orgu_type_id . $a_lang_code;
        if (isset(self::$instances[$cache_id])) {
            return self::$instances[$cache_id];
        } else {
            try {
                $trans = new self($a_orgu_type_id, $a_lang_code);
                self::$instances[$cache_id] = $trans;
                return $trans;
            } catch (ilOrgUnitTypeException $e) {
                return null;
            }
        }
    }

    /**
     * Get all translation objects for a given OrgUnit type ID
     *
     * @param int $a_orgu_type_id
     * @return array
     */
    public static function getAllTranslations($a_orgu_type_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        /** @var ilDB $ilDB */
        $sql = 'SELECT DISTINCT lang FROM ' . self::TABLE_NAME . ' WHERE orgu_type_id = ' . $ilDB->quote($a_orgu_type_id, 'integer');
        $set = $ilDB->query($sql);
        $objects = array();
        while ($rec = $ilDB->fetchObject($set)) {
            $trans_obj = new ilOrgUnitTypeTranslation($a_orgu_type_id, $rec->lang);
            $cache_id = $a_orgu_type_id . $rec->lang;
            self::$instances[$cache_id] = $trans_obj;
            $objects[] = $trans_obj;
        }
        return $objects;
    }

    /**
     * Checks if there exists a translation for a given member/value/lang triple
     * for any other OrgUnit than the OrgUnit ID provided.
     *
     * @param int $a_orgu_type_id
     * @param string $a_member
     * @param string $a_value
     * @param string $a_lang
     * @return bool
     */
    public static function exists($a_orgu_type_id, $a_member, $a_lang, $a_value)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        /** @var ilDB $ilDB */
        $sql = 'SELECT * FROM ' . self::TABLE_NAME . '
                WHERE orgu_type_id != ' . $ilDB->quote($a_orgu_type_id, 'integer') . '
                AND member = ' . $ilDB->quote($a_member, 'text') . '
                AND lang = ' . $ilDB->quote($a_lang, 'text') . '
                AND value = ' . $ilDB->quote($a_value, 'text');
        $set = $ilDB->query($sql);
        return ($ilDB->numRows($set)) ? true : false;
    }


    /**
     * Get translated value for a member, returns null if no translation exists.
     *
     * @param string $a_member Name of the variable, e.g. title,description
     * @return string|null
     */
    public function getMember($a_member)
    {
        return (isset($this->members[$a_member])) ? (string) $this->members[$a_member] : null;
    }

    /**
     * Set translation value for a member, either update or add value
     *
     * @param string $a_member Name of the variable, e.g. title,description
     * @param string $a_value Value of the translation
     */
    public function setMember($a_member, $a_value)
    {
        $is_new = !isset($this->members[$a_member]);
        $this->members[$a_member] = (string) $a_value;
        $this->trackChange($a_member, $is_new);
    }

    /**
     * Insert all translated member into database
     */
    public function create()
    {
        foreach ($this->members as $member => $value) {
            $this->insertMember($member, $value);
        }
        $this->resetTrackChanges();
    }

    /**
     * Update translations in database. Newly added members are inserted.
     */
    public function update()
    {
        foreach ($this->changes as $changed_member) {
            // Check if the member needs to be updated or inserted into database
            if (in_array($changed_member, $this->members_new)) {
                $this->insertMember($changed_member, $this->getMember($changed_member));
            } else {
                $this->updateMember($changed_member, $this->getMember($changed_member));
            }
        }
        $this->resetTrackChanges();
    }

    /**
     * Delete object
     */
    public function delete()
    {
        $sql = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE orgu_type_id = ' . $this->db->quote($this->getOrguTypeId(), 'integer') .
            ' AND lang = ' . $this->db->quote($this->getLang(), 'text');
        $this->db->manipulate($sql);
    }


    /**
     * Delete every translation existing for a given OrgUnit type id
     *
     * @param $a_orgu_type_id
     */
    public static function deleteAllTranslations($a_orgu_type_id)
    {
        global $DIC;
        $ilDB = $DIC['ilDB'];
        /** @var $ilDB ilDB */
        $sql = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE orgu_type_id = ' . $ilDB->quote($a_orgu_type_id, 'integer');
        $ilDB->manipulate($sql);
    }


    /**
     * Protected
     */

    /**
     * Insert a (member,value) pair in database
     *
     * @param $member
     * @param $value
     */
    protected function insertMember($member, $value)
    {
        $this->db->insert(self::TABLE_NAME, array(
            'orgu_type_id' => array('integer', $this->getOrguTypeId()),
            'lang' => array('text', $this->getLang()),
            'member' => array('text', $member),
            'value' => array('text', $value),
        ));
    }


    /**
     * Update a (member,value) pair in database
     *
     * @param $member
     * @param $value
     */
    protected function updateMember($member, $value)
    {
        $this->db->update(self::TABLE_NAME, array(
            'value' => array('text', $value),
        ), array(
            'orgu_type_id' => array('integer', $this->getOrguTypeId()),
            'lang' => array('text', $this->getLang()),
            'member' => array('text', $member),
        ));
    }

    /**
     * Track a member that was either updated or added
     *
     * @param string $a_member Name of a variable, e.g. title,description
     * @param bool $is_new True if the member did not exist before
     */
    protected function trackChange($a_member, $is_new)
    {
        if (!in_array($a_member, $this->changes)) {
            $this->changes[] = $a_member;
        }
        if ($is_new && !in_array($a_member, $this->members_new)) {
            $this->members_new[] = $a_member;
        }
    }

    /**
     * Reset tracked members
     */
    protected function resetTrackChanges()
    {
        $this->changes = array();
        $this->members_new = array();
    }

    /**
     * Read object data from database
     *
     * @throws ilOrgUnitTypeException
     */
    protected function read()
    {
        $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE orgu_type_id = ' . $this->db->quote($this->orgu_type_id, 'integer') .
            ' AND lang = ' . $this->db->quote($this->lang, 'text');
        $set = $this->db->query($sql);
        if (!$this->db->numRows($set)) {
            throw new ilOrgUnitTypeException("OrgUnit type translation for OrgUnit type {$this->orgu_type_id} and language {$this->lang} does not exist in database");
        }
        while ($rec = $this->db->fetchObject($set)) {
            $this->members[$rec->member] = (string) $rec->value;
        }
    }


    /**
     * Getters & Setters
     */


    /**
     * @param string $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return string
     */
    public function getLang()
    {
        return $this->lang;
    }


    /**
     * @return array
     */
    public function getMembers()
    {
        return $this->members;
    }


    /**
     * @return int
     */
    public function getOrguTypeId()
    {
        return $this->orgu_type_id;
    }

    /**
     * @param int $id
     */
    public function setOrguTypeId($id)
    {
        $this->orgu_type_id = (int) $id;
    }
}
