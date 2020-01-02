<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 *
 * @author Stefan Meyer <meyer@leifos.com>
 *
 * @ingroup ServicesAdvancedMetaData
 */
class ilAdvancedMDRecordObjectOrderings
{
    /**
     * @var \ilDBInterface
     */
    private $db;

    /**
     * @var array
     */
    private $record_position_map = [];

    /**
     * ilAdvancedMDRecordObjectOrderings constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
    }


    /**
     * Delete entries by obj_id
     * @param int $obj_id
     *
     * @throws \ilDatabaseException
     */
    public function deleteByObjId(int $obj_id)
    {
        $query = 'DELETE FROM adv_md_record_obj_ord ' .
            'WHERE obj_id = ' . $this->db->quote($obj_id, 'integer');
        $this->db->manipulate($query);
    }


    /**
     * @param array $records
     * @param int obj_id
     */
    public function sortRecords(array $records, int $obj_id = null)
    {
        // if local custom meta is not enabled use global sorting
        $use_global = true;
        if ($obj_id) {
            if (ilContainer::_lookupContainerSetting(
                $obj_id,
                ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                false
            )) {
                $use_global = false;
            }
        }
        if ($use_global) {
            usort(
                $records,
                [
                    __CLASS__,
                    'compareRecords'
                ]
            );
            return $records;
        } else {
            $this->readPositionsForObject($obj_id);
            usort(
                $records,
                [
                    __CLASS__,
                    'compareLocalRecords'
                ]
            );
        }

        return $records;
    }

    /**
     * @param \ilAdvancedMDRecord $a
     * @param \ilAdvancedMDRecord $b
     * @return int
     */
    public function compareRecords(\ilAdvancedMDRecord $a, \ilAdvancedMDRecord $b) : int
    {
        if ($a->getGlobalPosition() == null) {
            $a->setGlobalPosition(999);
        }
        if ($b->getGlobalPosition() == null) {
            $b->setGlobalPosition(999);
        }

        if ($a->getGlobalPosition() == $b->getGlobalPosition()) {
            return 0;
        }
        if ($a->getGlobalPosition() < $b->getGlobalPosition()) {
            return -1;
        }
        if ($a->getGlobalPosition() > $b->getGlobalPosition()) {
            return 1;
        }
    }

    /**
     * @param ilAdvancedMDRecord $a
     * @param ilAdvancedMDRecord $b
     * @return int
     */
    public function compareLocalRecords(\ilAdvancedMDRecord $a, \ilAdvancedMDRecord $b) : int
    {
        $local_pos_a = isset($this->record_position_map[$a->getRecordId()]) ?
            $this->record_position_map[$a->getRecordId()] :
            (
                $a->getGlobalPosition() ?
                    $a->getGlobalPosition() :
                    999
            );
        $local_pos_b = isset($this->record_position_map[$b->getRecordId()]) ?
            $this->record_position_map[$b->getRecordId()] :
            (
                $b->getGlobalPosition() ?
                $b->getGlobalPosition() :
                999
            );
        if ($local_pos_a == $local_pos_b) {
            return 0;
        }
        if ($local_pos_a < $local_pos_b) {
            return -1;
        }
        if ($local_pos_a > $local_pos_b) {
            return 1;
        }
    }

    /**
     * Read local positions for object
     * @param int $obj_id
     *
     */
    protected function readPositionsForObject(int $obj_id)
    {
        $query = 'SELECT record_id, position FROM adv_md_record_obj_ord ' .
            'WHERE obj_id = ' . $this->db->quote($obj_id, 'integer');
        $res = $this->db->query($query);

        $this->record_position_map = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->record_position_map[$row->record_id] = $row->position;
        }
    }
}
