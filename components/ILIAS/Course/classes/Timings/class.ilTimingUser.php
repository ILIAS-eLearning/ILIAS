<?php

declare(strict_types=0);
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
 * TableGUI class for timings administration
 * @author  Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 * @ingroup ModulesCourse
 */
class ilTimingUser
{
    private int $ref_id = 0;
    private int $usr_id = 0;
    private ilDateTime $start;
    private ilDateTime $end;

    private bool $is_scheduled = false;

    protected ilDBInterface $db;

    public function __construct(int $a_ref_id, int $a_usr_id)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->ref_id = $a_ref_id;
        $this->usr_id = $a_usr_id;

        $this->start = new ilDateTime(0, IL_CAL_UNIX);
        $this->end = new ilDateTime(0, IL_CAL_UNIX);
        $this->read();
    }

    public function getUserId(): int
    {
        return $this->usr_id;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function isScheduled(): bool
    {
        return $this->is_scheduled;
    }

    /**
     * Use to set start date
     */
    public function getStart(): ilDateTime
    {
        return $this->start;
    }

    /**
     * Use to set date
     */
    public function getEnd(): ilDateTime
    {
        return $this->end;
    }

    public function create(): void
    {
        if ($this->isScheduled()) {
            $this->update();
            return;
        }

        $query = 'INSERT INTO crs_timings_user (ref_id, usr_id, sstart, ssend ) VALUES ( ' .
            $this->db->quote($this->getRefId(), 'integer') . ', ' .
            $this->db->quote($this->getUserId(), 'integer') . ', ' .
            $this->db->quote($this->getStart()->get(IL_CAL_UNIX), 'integer') . ', ' .
            $this->db->quote($this->getEnd()->get(IL_CAL_UNIX), 'integer') . ' ' .
            ')';
        $this->db->manipulate($query);
        $this->is_scheduled = true;
    }

    public function update(): void
    {
        if (!$this->isScheduled()) {
            $this->create();
            return;
        }

        $query = 'UPDATE crs_timings_user ' .
            'SET sstart = ' . $this->db->quote($this->getStart()->get(IL_CAL_UNIX), 'integer') . ', ' .
            'ssend = ' . $this->db->quote($this->getEnd()->get(IL_CAL_UNIX), 'integer') . ' ' .
            'WHERE ref_id = ' . $this->db->quote($this->getRefId(), 'integer') . ' ' .
            'AND usr_id = ' . $this->db->quote($this->getUserId(), 'integer');
        $this->db->manipulate($query);
    }

    public function delete(): void
    {
        $query = 'DELETE FROM crs_timings_user ' . ' ' .
            'WHERE ref_id = ' . $this->db->quote($this->getRefId(), 'integer') . ' ' .
            'AND usr_id = ' . $this->db->quote($this->getUserId(), 'integer');
        $this->db->manipulate($query);
        $this->is_scheduled = false;
    }

    public function read(): void
    {
        $query = 'SELECT * FROM crs_timings_user ' .
            'WHERE ref_id = ' . $this->db->quote($this->getRefId(), 'integer') . ' ' .
            'AND usr_id = ' . $this->db->quote($this->getUserId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->is_scheduled = true;
            $this->start = new ilDateTime((int) $row->sstart, IL_CAL_UNIX);
            $this->end = new ilDateTime((int) $row->ssend, IL_CAL_UNIX);
        }
    }
}
