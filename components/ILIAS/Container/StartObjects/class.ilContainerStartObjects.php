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
 * ilContainerStartObjects
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilContainerStartObjects
{
    protected ilTree $tree;
    protected ilDBInterface $db;
    protected ilObjectDataCache $obj_data_cache;
    protected ilLogger $log;
    protected int $ref_id;
    protected int $obj_id;
    protected array $start_objs = [];

    public function __construct(
        int $a_object_ref_id,
        int $a_object_id
    ) {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->db = $DIC->database();
        $this->obj_data_cache = $DIC["ilObjDataCache"];
        $this->log = $DIC["ilLog"];
        $this->setRefId($a_object_ref_id);
        $this->setObjId($a_object_id);

        $this->read();
    }

    protected function setObjId(int $a_id): void
    {
        $this->obj_id = $a_id;
    }

    public function getObjId(): int
    {
        return $this->obj_id;
    }

    protected function setRefId(int $a_ref_id): void
    {
        $this->ref_id = $a_ref_id;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function getStartObjects(): array
    {
        return $this->start_objs ?: [];
    }

    protected function read(): void
    {
        $tree = $this->tree;
        $ilDB = $this->db;

        $this->start_objs = [];

        $query = "SELECT * FROM crs_start" .
            " WHERE crs_id = " . $ilDB->quote($this->getObjId(), 'integer') .
            " ORDER BY pos, crs_start_id";
        $res = $ilDB->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($tree->isInTree($row->item_ref_id)) {
                $this->start_objs[$row->crs_start_id]['item_ref_id'] = (int) $row->item_ref_id;
            } else {
                $this->delete((int) $row->item_ref_id);
            }
        }
    }

    public function delete(int $a_crs_start_id): void
    {
        $ilDB = $this->db;

        $query = "DELETE FROM crs_start" .
            " WHERE crs_start_id = " . $ilDB->quote($a_crs_start_id, 'integer') .
            " AND crs_id = " . $ilDB->quote($this->getObjId(), 'integer');
        $ilDB->manipulate($query);
    }

    public function deleteItem(int $a_item_ref_id): void
    {
        $ilDB = $this->db;

        $query = "DELETE FROM crs_start" .
            " WHERE crs_id = " . $ilDB->quote($this->getObjId(), 'integer') .
            " AND item_ref_id = " . $ilDB->quote($a_item_ref_id, 'integer');
        $ilDB->manipulate($query);
    }

    public function exists(int $a_item_ref_id): bool
    {
        $ilDB = $this->db;

        $query = "SELECT * FROM crs_start" .
            " WHERE crs_id = " . $ilDB->quote($this->getObjId(), 'integer') .
            " AND item_ref_id = " . $ilDB->quote($a_item_ref_id, 'integer');
        $res = $ilDB->query($query);

        return (bool) $res->numRows();
    }

    public function add(int $a_item_ref_id): bool
    {
        $ilDB = $this->db;

        if ($a_item_ref_id) {
            $max_pos = $ilDB->query("SELECT max(pos) pos FROM crs_start" .
                " WHERE crs_id = " . $ilDB->quote($this->getObjId(), "integer"));
            $max_pos = $ilDB->fetchAssoc($max_pos);
            $max_pos = ((int) $max_pos["pos"]) + 10;

            $next_id = $ilDB->nextId('crs_start');
            $query = "INSERT INTO crs_start" .
                " (crs_start_id,crs_id,item_ref_id,pos)" .
                " VALUES" .
                " (" . $ilDB->quote($next_id, 'integer') .
                ", " . $ilDB->quote($this->getObjId(), 'integer') .
                ", " . $ilDB->quote($a_item_ref_id, 'integer') .
                ", " . $ilDB->quote($max_pos, 'integer') .
                ")";
            $ilDB->manipulate($query);
            return true;
        }
        return false;
    }

    public function setObjectPos(
        int $a_start_id,
        int $a_pos
    ): void {
        $ilDB = $this->db;

        if (!$a_start_id || !$a_pos) {
            return;
        }

        $ilDB->manipulate("UPDATE crs_start" .
            " SET pos = " . $ilDB->quote($a_pos, "integer") .
            " WHERE crs_id = " . $ilDB->quote($this->getObjId(), 'integer') .
            " AND crs_start_id = " . $ilDB->quote($a_start_id, 'integer'));
    }

    public function getPossibleStarters(): array
    {
        $poss_items = [];
        foreach (ilObjectActivation::getItems($this->getRefId(), false) as $node) {
            switch ($node['type']) {
                case 'lm':
                case 'sahs':
                case 'svy':
                case 'copa':
                case 'tst':
                    $poss_items[] = $node['ref_id'];
                    break;
            }
        }
        return $poss_items;
    }

    public function allFullfilled(int $a_user_id): bool
    {
        foreach ($this->getStartObjects() as $item) {
            if (!$this->isFullfilled($a_user_id, $item['item_ref_id'])) {
                return false;
            }
        }
        return true;
    }

    public function isFullfilled(int $a_user_id, int $a_item_id): bool
    {
        $ilObjDataCache = $this->obj_data_cache;

        $obj_id = $ilObjDataCache->lookupObjId($a_item_id);
        $type = $ilObjDataCache->lookupType($obj_id);

        switch ($type) {
            case 'tst':
                if (!ilObjTestAccess::checkCondition($obj_id, 'finished', '', $a_user_id)) { // #14000
                    return false;
                }
                break;

            case 'svy':

                if (!ilObjSurveyAccess::_lookupFinished($obj_id, $a_user_id)) {
                    return false;
                }
                break;

            case 'copa':
            case 'sahs':
                if (!ilLPStatus::_hasUserCompleted($obj_id, $a_user_id)) {
                    return false;
                }
                break;

            default:
                $lm_continue = new ilCourseLMHistory($this->getRefId(), $a_user_id);
                $continue_data = $lm_continue->getLMHistory();
                if (!isset($continue_data[$a_item_id])) {
                    return false;
                }
                break;
        }

        return true;
    }

    public function cloneDependencies(
        int $a_target_id,
        int $a_copy_id
    ): void {
        $ilObjDataCache = $this->obj_data_cache;
        $ilLog = $this->log;

        $ilLog->write(__METHOD__ . ': Begin course start objects...');

        $new_obj_id = $ilObjDataCache->lookupObjId($a_target_id);
        $start = new self($a_target_id, $new_obj_id);

        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();
        foreach ($this->getStartObjects() as $data) {
            $item_ref_id = $data['item_ref_id'];
            if (isset($mappings[$item_ref_id]) && $mappings[$item_ref_id]) {
                $ilLog->write(__METHOD__ . ': Clone start object nr. ' . $item_ref_id);
                $start->add($mappings[$item_ref_id]);
            } else {
                $ilLog->write(__METHOD__ . ': No mapping found for start object nr. ' . $item_ref_id);
            }
        }
        $ilLog->write(__METHOD__ . ': ... end course start objects');
    }

    public static function isStartObject(
        int $a_container_id,
        int $a_item_ref_id
    ): bool {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT crs_start_id FROM crs_start ' .
            'WHERE crs_id = ' . $ilDB->quote($a_container_id, 'integer') . ' ' .
            'AND item_ref_id = ' . $ilDB->quote($a_item_ref_id, 'integer');
        $res = $ilDB->query($query);

        return $res->numRows() >= 1;
    }
}
