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
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ModulesCourse
 */
class ilMemberAgreement
{
    private ilDBInterface $db;
    private int $user_id;
    private int $obj_id;
    private string $type;
    private ilPrivacySettings $privacy;
    private bool $accepted = false;
    private int $acceptance_time = 0;

    public function __construct(int $a_usr_id, int $a_obj_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->db = $ilDB;
        $this->user_id = $a_usr_id;
        $this->obj_id = $a_obj_id;
        $this->type = ilObject::_lookupType($this->obj_id);

        $this->privacy = ilPrivacySettings::getInstance();

        if ($this->privacy->confirmationRequired($this->type) or ilCourseDefinedFieldDefinition::_hasFields($this->obj_id)) {
            $this->read();
        }
    }

    /**
     * Read user data by object id
     */
    public static function _readByObjId(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT * FROM member_agreement " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer');

        $res = $ilDB->query($query);
        $user_data = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $user_data[(int) $row->usr_id]['accepted'] = $row->accepted;
            $user_data[(int) $row->usr_id]['acceptance_time'] = $row->acceptance_time;
        }
        return $user_data;
    }

    /**
     * Check if there is any user agreement
     */
    public static function _hasAgreementsByObjId(int $a_obj_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT * FROM member_agreement " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . " " .
            "AND accepted = 1";

        $res = $ilDB->query($query);
        return (bool) $res->numRows();
    }

    /**
     * Check if there is any user agreement
     */
    public static function _hasAgreements() : bool
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT * FROM member_agreement " .
            "WHERE accepted = 1";

        $res = $ilDB->query($query);
        return (bool) $res->numRows();
    }

    /**
     * Check if user has accepted agreement
     */
    public static function _hasAccepted(int $a_usr_id, int $a_obj_id) : bool
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "SELECT accepted FROM member_agreement " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " " .
            "AND obj_id = " . $ilDB->quote($a_obj_id, 'integer');
        $res = $ilDB->query($query);
        $row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
        return (int) $row->accepted === 1;
    }

    /**
     * Lookup users who have accepted the agreement
     * @return int[]
     */
    public static function lookupAcceptedAgreements(int $a_obj_id) : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "SELECT usr_id FROM member_agreement " .
            "WHERE obj_id = " . $ilDB->quote($a_obj_id, 'integer') . ' ' .
            "AND accepted = 1 ";

        $res = $ilDB->query($query);
        $user_ids = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $user_ids[] = (int) $row['usr_id'];
        }
        return $user_ids;
    }

    /**
     * Delete all entries by user
     */
    public static function _deleteByUser(int $a_usr_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "DELETE FROM member_agreement " .
            "WHERE usr_id =" . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    /**
     * Delete all entries by obj_id
     */
    public static function _deleteByObjId(int $a_obj_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "DELETE FROM member_agreement " .
            "WHERE obj_id =" . $ilDB->quote($a_obj_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    /**
     * Reset all. Set all aggrement to 0.
     * This is called after global settings have been modified.
     */
    public static function _reset() : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "UPDATE member_agreement SET accepted = 0 ";
        $res = $ilDB->manipulate($query);
    }

    /**
     * Reset all agreements for a specific container
     */
    public static function _resetContainer(int $a_container_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = "UPDATE member_agreement " .
            "SET accepted = 0 " .
            "WHERE obj_id = " . $ilDB->quote($a_container_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }

    /**
     * set accepted
     */
    public function setAccepted(bool $a_status) : void
    {
        $this->accepted = $a_status;
    }

    /**
     * set acceptance time
     */
    public function setAcceptanceTime(int $a_timest) : void
    {
        $this->acceptance_time = $a_timest;
    }

    /**
     * Checks whether the agreement is accepted
     * This function return always true if no acceptance is required by global setting
     */
    public function agreementRequired() : bool
    {
        if (
            !$this->privacy->confirmationRequired($this->type) &&
            !ilCourseDefinedFieldDefinition::_hasFields($this->obj_id)
        ) {
            return false;
        }
        return !$this->accepted;
    }

    public function isAccepted() : bool
    {
        return $this->accepted;
    }

    public function getAcceptanceTime() : int
    {
        return $this->acceptance_time;
    }

    /**
     * save acceptance settings
     */
    public function save() : void
    {
        $this->delete();
        $query = "INSERT INTO member_agreement (usr_id,obj_id,accepted,acceptance_time) " .
            "VALUES( " .
            $this->db->quote($this->user_id, 'integer') . ", " .
            $this->db->quote($this->obj_id, 'integer') . ", " .
            $this->db->quote((int) $this->isAccepted(), 'integer') . ", " .
            $this->db->quote($this->getAcceptanceTime(), 'integer') . " " .
            ")";
        $this->db->manipulate($query);
    }

    /**
     * Delete entry
     */
    public function delete() : void
    {
        $query = "DELETE FROM member_agreement " .
            "WHERE usr_id = " . $this->db->quote($this->user_id, 'integer') . " " .
            "AND obj_id = " . $this->db->quote($this->obj_id, 'integer');
        $res = $this->db->manipulate($query);
    }

    /**
     * Read user entries
     */
    public function read() : void
    {
        $query = "SELECT * FROM member_agreement " .
            "WHERE usr_id = " . $this->db->quote($this->user_id, 'integer') . " " .
            "AND obj_id = " . $this->db->quote($this->obj_id, 'integer') . " ";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->accepted = (bool) $row->accepted;
            $this->acceptance_time = (int) $row->acceptance_time;
        }
    }
}
