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
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDRecordObjectOrdering
{
    private int $record_id;
    private int $obj_id;
    private int $position = 0;

    protected ilDBInterface $db;

    public function __construct(int $record_id, int $obj_id, ilDBInterface $db)
    {
        $this->record_id = $record_id;
        $this->obj_id = $obj_id;
        $this->db = $db;
    }

    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Save entry
     * @throws ilDatabaseException
     */
    public function save(): void
    {
        $this->delete();

        $query = 'INSERT INTO adv_md_record_obj_ord (record_id, obj_id, position ) ' .
            'VALUES ( ' .
            $this->db->quote($this->record_id, 'integer') . ', ' .
            $this->db->quote($this->obj_id, 'integer') . ', ' .
            $this->db->quote($this->position, 'integer') . ' ' .
            ')';

        $this->db->manipulate($query);
    }

    /**
     * Delete entry
     */
    public function delete(): void
    {
        $query = 'DELETE FROM adv_md_record_obj_ord WHERE ' .
            'record_id = ' . $this->db->quote($this->record_id, 'integer') . ' ' .
            'AND obj_id = ' . $this->db->quote($this->obj_id, 'integer');
        $this->db->manipulate($query);
    }
}
