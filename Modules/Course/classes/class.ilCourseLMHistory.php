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
 * class ilCourseLMHistory
 * @author  Stefan Meyer <meyer@leifos.com>
 * @version $Id$
 * @extends Object
 */
class ilCourseLMHistory
{
    private int $course_id = 0;
    private int $user_id = 0;

    protected ilDBInterface $db;

    public function __construct(int $crs_id, int $user_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->course_id = $crs_id;
        $this->user_id = $user_id;
    }

    public function getUserId() : int
    {
        return $this->user_id;
    }

    public function getCourseRefId() : int
    {
        return $this->course_id;
    }

    public static function _updateLastAccess(int $a_user_id, int $a_lm_ref_id, int $a_page_id) : bool
    {
        global $DIC;

        $tree = $DIC['tree'];
        $ilDB = $DIC['ilDB'];

        if (!$crs_ref_id = $tree->checkForParentType($a_lm_ref_id, 'crs')) {
            return true;
        }

        $ilDB->replace(
            "crs_lm_history",
            [
                "crs_ref_id" => ["integer", $crs_ref_id],
                "lm_ref_id" => ["integer", $a_lm_ref_id],
                "usr_id" => ["integer", $a_user_id]
            ],
            [
                "lm_page_id" => ["integer", $a_page_id],
                "last_access" => ["integer", time()]
            ]
        );

        return true;
    }

    public function getLastLM() : int
    {
        $query = "SELECT * FROM crs_lm_history " .
            "WHERE usr_id = " . $this->db->quote($this->getUserId(), 'integer') . " " .
            "AND crs_ref_id = " . $this->db->quote($this->getCourseRefId(), 'integer') . " " .
            "ORDER BY last_access ";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (int) $row->lm_ref_id;
        }
        return 0;
    }

    public function getLMHistory() : array
    {
        $query = "SELECT * FROM crs_lm_history " .
            "WHERE usr_id = " . $this->db->quote($this->getUserId(), 'integer') . " " .
            "AND crs_ref_id = " . $this->db->quote($this->getCourseRefId(), 'integer') . "";

        $res = $this->db->query($query);
        $lm = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $lm[$row->lm_ref_id]['lm_ref_id'] = (int) $row->lm_ref_id;
            $lm[$row->lm_ref_id]['lm_page_id'] = (int) $row->lm_page_id;
            $lm[$row->lm_ref_id]['last_access'] = (int) $row->last_access;
        }
        return $lm;
    }

    public static function _deleteUser(int $a_usr_id) : void
    {
        global $DIC;

        $ilDB = $DIC->database();
        $query = "DELETE FROM crs_lm_history WHERE usr_id = " . $ilDB->quote($a_usr_id, 'integer') . " ";
        $res = $ilDB->manipulate($query);
    }
}
