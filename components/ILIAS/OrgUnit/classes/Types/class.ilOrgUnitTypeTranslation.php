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
 ********************************************************************
 */

/**
 * Class ilOrgUnitTypeTranslation
 * This class represents a translation for a given ilOrgUnit object and language.
 * @author: Stefan Wanzenried <sw@studer-raimann.ch>
 */
class ilOrgUnitTypeTranslation
{
    public const TABLE_NAME = 'orgu_types_trans';
    protected int $orgu_type_id;
    protected string $lang = '';
    protected array $members = [];
    protected array $changes = [];
    protected array $members_new = [];
    protected ilDBInterface $db;
    protected \ILIAS\DI\LoggingServices $log;
    /** @var self[] */
    protected static array $instances = [];

    public function __construct(int $a_org_type_id = 0, string $a_lang_code = '')
    {
        global $DIC;
        $this->db = $DIC->database();
        $this->log = $DIC->logger();
        if ($a_org_type_id && $a_lang_code) {
            $this->orgu_type_id = (int) $a_org_type_id;
            $this->lang = $a_lang_code;
            $this->read();
        }
    }


    /**
     * Get instance of an ilOrgUnitType object
     * Returns object from cache or from database, returns null if no object was found
     * @param int    $a_orgu_type_id ID of an ilOrgUnitType object
     * @param string $a_lang_code    Language code
     * @return ilOrgUnitTypeTranslation|null
     */
    public static function getInstance(int $a_orgu_type_id, string $a_lang_code): ?ilOrgUnitTypeTranslation
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
     * @param int $a_orgu_type_id
     * @return array
     */
    public static function getAllTranslations(int $a_orgu_type_id): array
    {
        global $DIC;
        /** @var ilDBInterface $db **/;
        $db = $DIC->database();

        $sql = 'SELECT DISTINCT lang FROM ' . self::TABLE_NAME . ' WHERE orgu_type_id = ' . $db->quote(
            $a_orgu_type_id,
            'integer'
        );
        $set = $db->query($sql);
        $objects = array();
        while ($rec = $db->fetchObject($set)) {
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
     * @param int    $a_orgu_type_id
     * @param string $a_member
     * @param string $a_value
     * @param string $a_lang
     * @return bool
     */
    public static function exists(int $a_orgu_type_id, string $a_member, string $a_lang, string $a_value): bool
    {
        global $DIC;
        /** @var ilDBInterface $db **/;
        $db = $DIC->database();

        $sql = 'SELECT * FROM ' . self::TABLE_NAME . '
                WHERE orgu_type_id != ' . $db->quote($a_orgu_type_id, 'integer') . '
                AND member = ' . $db->quote($a_member, 'text') . '
                AND lang = ' . $db->quote($a_lang, 'text') . '
                AND value = ' . $db->quote($a_value, 'text');
        $set = $db->query($sql);

        return ($db->numRows($set)) ? true : false;
    }

    /**
     * Get translated value for a member, returns null if no translation exists.
     * @param string $a_member Name of the variable, e.g. title,description
     * @return string|null
     */
    public function getMember(string $a_member): ?string
    {
        return (isset($this->members[$a_member])) ? (string) $this->members[$a_member] : null;
    }

    /**
     * Set translation value for a member, either update or add value
     * @param string $a_member Name of the variable, e.g. title,description
     * @param string $a_value  Value of the translation
     */
    public function setMember(string $a_member, string $a_value)
    {
        $is_new = !isset($this->members[$a_member]);
        $this->members[$a_member] = (string) $a_value;
        $this->trackChange($a_member, $is_new);
    }

    /**
     * Insert all translated member into database
     */
    public function create(): void
    {
        foreach ($this->members as $member => $value) {
            $this->insertMember($member, $value);
        }
        $this->resetTrackChanges();
    }

    /**
     * Update translations in database. Newly added members are inserted.
     */
    public function update(): void
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
    public function delete(): void
    {
        $sql = 'DELETE FROM ' . self::TABLE_NAME . ' WHERE orgu_type_id = ' . $this->db->quote(
            $this->getOrguTypeId(),
            'integer'
        ) .
            ' AND lang = ' . $this->db->quote($this->getLang(), 'text');
        $this->db->manipulate($sql);
    }

    /**
     * Delete every translation existing for a given OrgUnit type id
     */
    public static function deleteAllTranslations(string $a_orgu_type_id): void
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
     */
    protected function insertMember(string $member, string $value): void
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
     */
    protected function updateMember(string $member, string $value): void
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
     * @param string $a_member Name of a variable, e.g. title,description
     * @param bool   $is_new   True if the member did not exist before
     */
    protected function trackChange(string $a_member, bool $is_new): void
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
    protected function resetTrackChanges(): void
    {
        $this->changes = array();
        $this->members_new = array();
    }

    /**
     * Read object data from database
     * @throws ilOrgUnitTypeException
     */
    protected function read(): void
    {
        $sql = 'SELECT * FROM ' . self::TABLE_NAME . ' WHERE orgu_type_id = ' . $this->db->quote(
            $this->orgu_type_id,
            'integer'
        ) .
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

    public function setLang(string $lang): void
    {
        $this->lang = $lang;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getMembers(): array
    {
        return $this->members;
    }

    public function getOrguTypeId(): int
    {
        return $this->orgu_type_id;
    }

    public function setOrguTypeId(int $id): void
    {
        $this->orgu_type_id = $id;
    }
}
