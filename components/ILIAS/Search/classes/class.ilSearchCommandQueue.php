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
*
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup ServicesSearch
*/
class ilSearchCommandQueue
{
    private static ?self $instance = null;

    protected ilDBInterface $db;

    /**
     * Constructor
     */
    protected function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }

    /**
     * get singleton instance
     */
    public static function factory(): ilSearchCommandQueue
    {
        if (self::$instance instanceof ilSearchCommandQueue) {
            return self::$instance;
        }
        return self::$instance = new ilSearchCommandQueue();
    }

    /**
     * update / save new entry
     */
    public function store(ilSearchCommandQueueElement $element): void
    {
        $query = "SELECT obj_id, obj_type FROM search_command_queue " .
            "WHERE obj_id = " . $this->db->quote($element->getObjId(), 'integer') . " " .
            "AND obj_type = " . $this->db->quote($element->getObjType(), 'text');
        $res = $this->db->query($query);
        if ($res->numRows()) {
            $this->update($element);
        } else {
            $this->insert($element);
        }
    }

    /**
     * Insert new entry
     */
    protected function insert(ilSearchCommandQueueElement $element): void
    {
        $query = "INSERT INTO search_command_queue (obj_id,obj_type,sub_id,sub_type,command,last_update,finished) " .
            "VALUES( " .
            $this->db->quote($element->getObjId(), 'integer') . ", " .
            $this->db->quote($element->getObjType(), 'text') . ", " .
            "0, " .
            "''," .
            $this->db->quote($element->getCommand(), 'text') . ", " .
            $this->db->now() . ", " .
            "0 " .
            ")";
        $res = $this->db->manipulate($query);
    }

    /**
     * Update existing entry
     */
    protected function update(ilSearchCommandQueueElement $element): void
    {
        $query = "UPDATE search_command_queue " .
            "SET command = " . $this->db->quote($element->getCommand(), 'text') . ", " .
            "last_update = " . $this->db->now() . ", " .
            "finished = " . $this->db->quote(0, 'integer') . " " .
            "WHERE obj_id = " . $this->db->quote($element->getObjId(), 'integer') . " " .
            "AND obj_type = " . $this->db->quote($element->getObjType(), 'text');
        $res = $this->db->manipulate($query);
    }
}
