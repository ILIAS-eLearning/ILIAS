<?php declare(strict_types=0);
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
 * class ilTimingAccepted
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilTimingAccepted
{
    protected ilDBInterface $db;

    private int $obj_id = 0;
    private int $user_id = 0;
    private bool $visible = false;
    private string $remark = '';
    private bool $accepted = false;

    public function __construct(int $crs_id, int $a_usr_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->obj_id = $crs_id;
        $this->user_id = $a_usr_id;
        $this->__read();
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function getCourseId() : int
    {
        return $this->obj_id;
    }

    public function accept(bool $a_status) : void
    {
        $this->accepted = $a_status;
    }

    public function isAccepted() : bool
    {
        return $this->accepted;
    }

    public function setRemark(string $a_remark) : void
    {
        $this->remark = $a_remark;
    }

    public function getRemark() : string
    {
        return $this->remark;
    }

    public function setVisible(bool $a_visible) : void
    {
        $this->visible = $a_visible;
    }

    public function isVisible() : bool
    {
        return $this->visible;
    }

    public function update() : void
    {
        ilTimingAccepted::_delete($this->getCourseId(), $this->getUserId());
        $this->create();
    }

    public function create() : void
    {
        $query = "INSERT INTO crs_timings_usr_accept (crs_id,usr_id,visible,accept,remark) " .
            "VALUES( " .
            $this->db->quote($this->getCourseId(), 'integer') . ", " .
            $this->db->quote($this->getUserId(), 'integer') . ", " .
            $this->db->quote($this->isVisible(), 'integer') . ", " .
            $this->db->quote($this->isAccepted(), 'integer') . ", " .
            $this->db->quote($this->getRemark(), 'text') . " " .
            ")";
        $res = $this->db->manipulate($query);
    }

    public function delete() : void
    {
        ilTimingAccepted::_delete($this->getCourseId(), $this->getUserId());
    }

    public function _delete(int $a_crs_id, int $a_usr_id) : void
    {
        $query = "DELETE FROM crs_timings_usr_accept " .
            "WHERE crs_id = " . $this->db->quote($a_crs_id, 'integer') . " " .
            "AND usr_id = " . $this->db->quote($a_usr_id, 'integer') . " ";
        $res = $this->db->manipulate($query);
    }

    public function _deleteByCourse(int $a_crs_id) : void
    {
        $query = "DELETE FROM crs_timings_usr_accept " .
            "WHERE crs_id = " . $this->db->quote($a_crs_id, 'integer') . " ";
        $res = $this->db->manipulate($query);
    }

    public static function _deleteByUser(int $a_usr_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "DELETE FROM crs_timings_usr_accept " .
            "WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . "";
        $res = $ilDB->manipulate($query);
    }

    public function __read() : void
    {
        if ($this->obj_id <= 0 || $this->user_id <= 0) {
            return;
        }
        $query = "SELECT * FROM crs_timings_usr_accept " .
            "WHERE crs_id = " . $this->db->quote($this->getCourseId(), 'integer') . " " .
            "AND usr_id = " . $this->db->quote($this->getUserId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->setVisible((bool) $row->visible);
            $this->setRemark((string) $row->remark);
            $this->accept((bool) $row->accept);
        }
    }
}
