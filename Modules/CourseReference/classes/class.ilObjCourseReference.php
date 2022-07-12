<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

include_once('./Services/ContainerReference/classes/class.ilContainerReference.php');

/**
*
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*
* @ingroup ModulesCourseReference
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
    public function isMemberUpdateEnabled() : bool
    {
        return $this->member_update;
    }

    /**
     * @param int $obj_id
     * @return bool
     */
    public static function lookupMemberUpdateEnabled(int $obj_id) : bool
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
    public function create() : int
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
    public function read() : void
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
    public function update() : bool
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
    public function delete() : bool
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
    public function cloneObject(int $a_target_id, int $a_copy_id = 0, bool $a_omit_tree = false) : ?ilObject
    {
        $new_obj = parent::cloneObject($a_target_id, $a_copy_id, $a_omit_tree);
        $new_obj->enableMemberUpdate($this->isMemberUpdateEnabled());
        $new_obj->update();
        return $new_obj;
    }
}
