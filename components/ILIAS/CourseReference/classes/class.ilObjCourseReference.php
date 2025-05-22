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

/**
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup components\ILIASCourseReference
*/
class ilObjCourseReference extends ilContainerReference
{
    /**
     * @var bool
     */
    private $member_update = false;


    /**
     * Constructor
     * @param int $a_id reference id
     * @param bool $a_call_by_reference
     * @return void
     */
    public function __construct($a_id = 0, $a_call_by_reference = true)
    {
        global $ilDB;

        $this->type = 'crsr';
        parent::__construct($a_id, $a_call_by_reference);
    }

    /**
     * @param bool $status
     */
    public function enableMemberUpdate(bool $status)
    {
        $this->member_update = $status;
    }

    /**
     * @return bool
     */
    public function isMemberUpdateEnabled(): bool
    {
        return $this->member_update;
    }

    /**
     * @param int $obj_id
     * @return bool
     */
    public static function lookupMemberUpdateEnabled(int $obj_id): bool
    {
        global $DIC;

        $db = $DIC->database();

        $query = 'select member_update from crs_reference_settings where ' .
            'obj_id = ' . $db->quote($obj_id, ilDBConstants::T_INTEGER);
        $res = $db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->member_update;
        }
        return false;
    }


    /**
     * @return @inheritdoc
     */
    public function create(): int
    {
        $id = parent::create();

        $query = 'INSERT INTO crs_reference_settings (obj_id, member_update ) ' .
            'VALUES ( ' .
            $this->db->quote($id, ilDBConstants::T_INTEGER) . ', ' .
            $this->db->quote((int) $this->isMemberUpdateEnabled(), ilDBConstants::T_INTEGER) . ' ' .
            ')';
        $this->db->manipulate($query);
        return $id;
    }

    /**
     * @inheritdoc
     */
    public function read(): void
    {
        parent::read();

        $query = 'SELECT * FROM crs_reference_settings ' .
            'WHERE obj_id = ' . $this->db->quote($this->getId(), ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->enableMemberUpdate($row->member_update);
        }
    }

    /**
     * @inheritdoc
     */
    public function update(): bool
    {
        parent::update();
        $query = 'UPDATE crs_reference_settings ' .
            'SET member_update = ' . $this->db->quote((int) $this->isMemberUpdateEnabled(), ilDBConstants::T_INTEGER) . ' ' .
            'WHERE obj_id = ' . $this->db->quote((int) $this->getId(), ilDBConstants::T_INTEGER);
        $this->db->manipulate($query);

        ilLoggerFactory::getLogger('crs')->info($query);

        return true;
    }

    /**
     * @inheritdoc
     */
    public function delete(): bool
    {
        if (!parent::delete()) {
            return false;
        }
        $query = 'DELETE FROM crs_reference_settings ' .
            'WHERE obj_id = ' . $this->db->quote($this->getId(), ilDBConstants::T_INTEGER);
        $this->db->manipulate($query);
        return true;
    }


    /**
     * @inheritdoc
     */
    public function cloneObject(int $a_target_id, int $a_copy_id = 0, bool $a_omit_tree = false): ?ilObject
    {
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
        $new_obj->enableMemberUpdate($this->isMemberUpdateEnabled());
        $new_obj->update();
        return $new_obj;
    }
}
