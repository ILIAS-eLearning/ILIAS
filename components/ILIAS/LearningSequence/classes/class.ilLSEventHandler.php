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
 * Handle events.
 */
class ilLSEventHandler
{
    protected ilTree $tree;

    public function __construct(ilTree $tree)
    {
        $this->tree = $tree;
    }

    /**
     * Find out, if a sub object is about to be deleted.
     * cleanup states.
     */
    public function handleObjectDeletion(array $parameter): void
    {
        $obj_deleted = $parameter['object'];
        $obj_ref_id = (int) $obj_deleted->getRefId();

        if (!$this->isExistingObject($obj_ref_id)) {
            return;
        }

        $parent_lso = $this->getParentLSOInfo($obj_ref_id);
        if (!is_null($parent_lso)) {
            $this->deleteLSOItem($obj_ref_id, (int) $parent_lso['ref_id']);
        }
    }

    /**
     * @param  array  $parameter [obj_id, ref_id, old_parent_ref_id]
     */
    public function handleObjectToTrash(array $parameter): void
    {
        $obj_ref_id = (int) $parameter['ref_id'];
        $old_parent_ref_id = (int) $parameter['old_parent_ref_id'];
        $parent_lso = $this->getParentLSOInfo($obj_ref_id);

        if (!$this->isExistingObject($obj_ref_id) || is_null($parent_lso)) {
            return;
        }

        if ($old_parent_ref_id) {
            $this->deleteLSOItem($obj_ref_id, $old_parent_ref_id);
        }
    }

    protected function isExistingObject(int $ref_id): bool
    {
        if (empty($ref_id) || !$this->tree->isInTree($ref_id)) {
            return false;
        }
        return true;
    }

    protected function deleteLSOItem(int $obj_ref_id, int $parent_lso_ref_id): void
    {
        $lso = $this->getInstanceByRefId($parent_lso_ref_id);
        $lso->getStateDB()->deleteForItem(
            $parent_lso_ref_id,
            $obj_ref_id
        );

        // remove orphaned adaptive path entries pointing to the deleted object
        $lso_obj_id = (int) \ilObject::_lookupObjId($parent_lso_ref_id);
        $this->getItemPathRepo()->deleteForItemRefId($lso_obj_id, $obj_ref_id);

        // remove orphaned visit-log entries pointing to the deleted object
        $this->deleteVisitsForItemRefId($lso_obj_id, $obj_ref_id);
    }

    public function handleParticipantDeletion(int $obj_id, int $usr_id): void
    {
        $lso = $this->getInstanceByObjId($obj_id);
        $db = $lso->getStateDB();
        $db->deleteFor($lso->getRefId(), [$usr_id]);

        // reset the adaptive path of the removed participant
        $this->getItemPathRepo()->reset($usr_id, $obj_id);

        // reset the append-only visit log of the removed participant
        $this->resetVisits($usr_id, $obj_id);
    }

    protected function getItemPathRepo(): \ILIAS\LearningSequence\Content\Adaptive\LSOItemPath
    {
        global $DIC;
        return new \ILIAS\LearningSequence\Content\Adaptive\LSOItemPath($DIC->database());
    }

    protected function resetVisits(int $usr_id, int $lso_obj_id): void
    {
        global $DIC;
        $db = $DIC->database();
        $table = \ILIAS\LearningSequence\LearningMap\LSOLearningMapPosition::VISITS_TABLE;
        $db->manipulate(
            "DELETE FROM " . $table
            . " WHERE usr_id = " . $db->quote($usr_id, 'integer')
            . " AND lso_obj_id = " . $db->quote($lso_obj_id, 'integer')
        );
    }

    protected function deleteVisitsForItemRefId(int $lso_obj_id, int $ref_id): void
    {
        global $DIC;
        $db = $DIC->database();
        $table = \ILIAS\LearningSequence\LearningMap\LSOLearningMapPosition::VISITS_TABLE;
        $db->manipulate(
            "DELETE FROM " . $table
            . " WHERE lso_obj_id = " . $db->quote($lso_obj_id, 'integer')
            . " AND ref_id = " . $db->quote($ref_id, 'integer')
        );
    }

    public function handleClonedObject(ilObject $new_obj, ilObject $origin_obj): void
    {
        if ($this->getParentLSOInfo($new_obj->getRefId())
            && $this->getParentLSOInfo($origin_obj->getRefId())
        ) {
            $new_lso = $this->getInstanceByRefId(
                (int) $this->getParentLSOInfo($new_obj->getRefId())['ref_id']
            );
            $post_condition_db = $new_lso->getDI()['db.postconditions'];
            $post_condition = current($post_condition_db->select([$origin_obj->getRefId()]))
                ->withRefId($new_obj->getRefId());
            $post_condition_db->upsert([$post_condition]);
        }
    }

    /**
     * get the LSO up from $child_ref_id
     */
    protected function getParentLSOInfo(int $child_ref_id): ?array
    {
        if ($child_ref_id === 0) {
            return null;
        }

        foreach ($this->tree->getPathFull($child_ref_id) as $hop) {
            if ($hop['type'] === 'lso') {
                return $hop;
            }
        }
        return null;
    }

    /**
     * @return array<int|string, int|string>
     */
    protected function getRefIdsOfObjId(int $triggerer_obj_id): array
    {
        return ilObject::_getAllReferences($triggerer_obj_id);
    }

    protected function getInstanceByRefId(int $ref_id): ilObjLearningSequence
    {
        /** @var ilObjLearningSequence $obj */
        $obj = ilObjectFactory::getInstanceByRefId($ref_id);

        if (!$obj instanceof ilObjLearningSequence) {
            throw new LogicException("Object type should be ilObjLearningSequence. Actually is " . get_class($obj));
        }

        return $obj;
    }

    protected function getInstanceByObjId(int $obj_id): ilObjLearningSequence
    {
        $refs = array_keys(\ilObject::_getAllReferences($obj_id));
        $ref_id = array_shift($refs);

        /** @var ilObjLearningSequence $obj */
        $obj = ilObjectFactory::getInstanceByRefId($ref_id);

        if (!$obj instanceof ilObjLearningSequence) {
            throw new LogicException("Object type should be ilObjLearningSequence. Actually is " . get_class($obj));
        }

        return $obj;
    }
}
