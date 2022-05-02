<?php declare(strict_types=1);

/******************************************************************************
 *
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
 *     https://www.ilias.de
 *     https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

namespace ILIAS\Notifications;

use ilDBInterface;
use ilDBStatement;
use Iterator;

/**
 * @author Jan Posselt <jposselt@databay.de>
 */
class ilNotificationUserIterator implements Iterator
{
    /**
     * @var int[]
     */
    private array $userids;
    private ilDBStatement $rset;
    private ilDBInterface $db;
    private array $data;
    private string $module;

    public function __construct(string $module, array $userids = [])
    {
        global $ilDB;
        $this->db = $ilDB;
        $this->userids = $userids;
        $this->module = $module;
        $this->rewind();
    }

    public function __destruct()
    {
        $this->db->free($this->rset);
    }

    public function current() : array
    {
        return $this->data;
    }

    public function key() : int
    {
        return (int) $this->data['usr_id'];
    }

    public function next() : void
    {
    }

    public function rewind() : void
    {
        $query = 'SELECT usr_id, module, channel FROM ' . ilNotificationSetupHelper::$tbl_userconfig . ' WHERE module=%s AND ' . $this->db->in('usr_id', $this->userids, false, 'integer');
        $types = array('text');
        $values = array($this->module);
        $this->rset = $this->db->queryF($query, $types, $values);
    }

    public function valid() : bool
    {
        $this->data = $this->db->fetchAssoc($this->rset);
        return is_array($this->data);
    }
}
