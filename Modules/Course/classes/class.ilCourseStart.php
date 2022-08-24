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
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilCourseStart
{
    private int $ref_id;
    private int $id;
    private array $start_objs = [];

    protected ilDBInterface $db;
    protected ilLogger $logger;
    protected ilObjectDataCache $objectDataCache;
    protected ilTree $tree;

    public function __construct($a_course_ref_id, $a_course_obj_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->objectDataCache = $DIC['ilObjDataCache'];
        $this->logger = $DIC->logger()->crs();

        $this->ref_id = $a_course_ref_id;
        $this->id = $a_course_obj_id;
        $this->__read();
    }

    public function setId(int $a_id): void
    {
        $this->id = $a_id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setRefId(int $a_ref_id): void
    {
        $this->ref_id = $a_ref_id;
    }

    public function getRefId(): int
    {
        return $this->ref_id;
    }

    public function getStartObjects(): array
    {
        return $this->start_objs;
    }

    public function cloneDependencies(int $a_target_id, int $a_copy_id): void
    {
        $this->logger->debug('Begin course start objects...');

        $new_obj_id = $this->objectDataCache->lookupObjId($a_target_id);
        $start = new ilCourseStart($a_target_id, $new_obj_id);

        $cwo = ilCopyWizardOptions::_getInstance($a_copy_id);
        $mappings = $cwo->getMappings();
        foreach ($this->getStartObjects() as $data) {
            $item_ref_id = $data['item_ref_id'];
            if (isset($mappings[$item_ref_id]) && $mappings[$item_ref_id]) {
                $this->logger->debug('Clone start object nr. ' . $item_ref_id);
                $start->add($mappings[$item_ref_id]);
            } else {
                $this->logger->debug('No mapping found for start object nr. ' . $item_ref_id);
            }
        }
        $this->logger->debug('... end course start objects');
    }

    public function delete(int $a_crs_start_id): void
    {
        $query = "DELETE FROM crs_start " .
            "WHERE crs_start_id = " . $this->db->quote($a_crs_start_id, 'integer') . " " .
            "AND crs_id = " . $this->db->quote($this->getId(), 'integer') . " ";
        $res = $this->db->manipulate($query);
    }

    public function exists(int $a_item_ref_id): bool
    {
        $query = "SELECT * FROM crs_start " .
            "WHERE crs_id = " . $this->db->quote($this->getId(), 'integer') . " " .
            "AND item_ref_id = " . $this->db->quote($a_item_ref_id, 'integer') . " ";
        $res = $this->db->query($query);
        return (bool) $res->numRows();
    }

    public function add(int $a_item_ref_id): void
    {
        if ($a_item_ref_id) {
            $next_id = $this->db->nextId('crs_start');
            $query = "INSERT INTO crs_start (crs_start_id,crs_id,item_ref_id) " .
                "VALUES( " .
                $this->db->quote($next_id, 'integer') . ", " .
                $this->db->quote($this->getId(), 'integer') . ", " .
                $this->db->quote($a_item_ref_id, 'integer') . " " .
                ")";
            $res = $this->db->manipulate($query);
        }
    }

    public function getPossibleStarters(): array
    {
        $poss_items = [];
        foreach (ilObjectActivation::getItems($this->getRefId(), false) as $node) {
            switch ($node['type']) {
                case 'lm':
                case 'sahs':
                case 'svy':
                case 'tst':
                    $poss_items[] = $node['ref_id'];
                    break;
            }
        }
        return $poss_items;
    }

    public function allFullfilled($user_id): bool
    {
        foreach ($this->getStartObjects() as $item) {
            if (!$this->isFullfilled($user_id, $item['item_ref_id'])) {
                return false;
            }
        }
        return true;
    }

    public function isFullfilled(int $user_id, int $item_id): bool
    {
        $lm_continue = new ilCourseLMHistory($this->getRefId(), $user_id);
        $continue_data = $lm_continue->getLMHistory();

        $obj_id = $this->objectDataCache->lookupObjId($item_id);
        $type = $this->objectDataCache->lookupType($obj_id);

        switch ($type) {
            case 'tst':

                if (!ilObjTestAccess::checkCondition($obj_id, ilConditionHandler::OPERATOR_FINISHED, '', $user_id)) {
                    return false;
                }
                break;
            case 'svy':
                if (!ilObjSurveyAccess::_lookupFinished($obj_id, $user_id)) {
                    return false;
                }
                break;
            case 'sahs':
                if (!ilLPStatus::_hasUserCompleted($obj_id, $user_id)) {
                    return false;
                }
                break;

            default:
                if (!isset($continue_data[$item_id])) {
                    return false;
                }
        }
        return true;
    }

    public function __read(): void
    {
        $this->start_objs = array();
        $query = "SELECT * FROM crs_start " .
            "WHERE crs_id = " . $this->db->quote($this->getId(), 'integer') . " ";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            if ($this->tree->isInTree((int) $row->item_ref_id)) {
                $this->start_objs[(int) $row->crs_start_id]['item_ref_id'] = (int) $row->item_ref_id;
            } else {
                $this->delete((int) $row->item_ref_id);
            }
        }
    }
} // END class.ilObjCourseGrouping
