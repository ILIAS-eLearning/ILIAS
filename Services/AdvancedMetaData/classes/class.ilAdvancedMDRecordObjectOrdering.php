<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDRecordObjectOrdering
{
    /**
     * @var int
     */
    private $record_id;

    /**
     * @var int
     */
    private $obj_id;

    /**
     * @var int
     */
    private $position;


    /**
     * @var \ilDBInterface
     */
    private $db;


    /**
     * ilAdvancedMDRecordObjectOrdering constructor.
     * @param int $record_id
     * @param int $obj_id
     */
    public function __construct(int $record_id, int $obj_id, ilDBInterface $db)
    {
        $this->record_id = $record_id;
        $this->obj_id = $obj_id;

        $this->db = $db;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition() : int
    {
        return $this->position;
    }

    /**
     * Save entry
     * @throws ilDatabaseException
     */
    public function save()
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
    public function delete()
    {
        $query = 'DELETE FROM adv_md_record_obj_ord WHERE ' .
            'record_id = ' . $this->db->quote($this->record_id, 'integer') . ' ' .
            'AND obj_id = ' . $this->db->quote($this->obj_id, 'integer');
        $this->db->manipulate($query);
    }
}
