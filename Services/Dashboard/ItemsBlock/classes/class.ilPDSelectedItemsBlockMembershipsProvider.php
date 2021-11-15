<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

class ilPDSelectedItemsBlockMembershipsProvider implements ilPDSelectedItemsBlockProvider
{
    protected ilObjUser $actor;
    protected ilTree $tree;
    protected ilAccessHandler $access;

    public function __construct(ilObjUser $actor)
    {
        global $DIC;

        $this->actor = $actor;
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
    }

    /**
     * Gets all objects the current user is member of
     */
    protected function getObjectsByMembership(
        array $types = []
    ) : array {
        $items = array();

        if (is_array($types) && count($types)) {
            foreach ($types as $type) {
                switch ($type) {
                    case 'grp':
                        $items = array_merge(ilParticipants::_getMembershipByType($this->actor->getId(), 'grp'), $items);
                        break;
                    case 'crs':
                        $items = array_merge(ilParticipants::_getMembershipByType($this->actor->getId(), 'crs'), $items);
                        break;
                    default:
                        break;
                }
            }
        } else {
            $crs_mbs = ilParticipants::_getMembershipByType($this->actor->getId(), 'crs');
            $grp_mbs = ilParticipants::_getMembershipByType($this->actor->getId(), 'grp');
            $items = array_merge($crs_mbs, $grp_mbs);
        }

        $references = array();

        foreach ($items as $key => $obj_id) {
            $item_references = ilObject::_getAllReferences($obj_id);
            foreach ($item_references as $ref_id) {
                if (!$this->access->checkAccess('read', '', $ref_id) &&
                    !$this->access->checkAccess('visible', '', $ref_id)) {
                    continue;
                }
                if ($this->tree->isInTree($ref_id)) {
                    $object = ilObjectFactory::getInstanceByRefId($ref_id);

                    $parent_ref_id = $this->tree->getParentId($ref_id);
                    $par_left = $this->tree->getLeftValue($parent_ref_id);
                    $par_left = sprintf("%010d", $par_left);

                    if ($parent_ref_id != RECOVERY_FOLDER_ID) {
                        $references[$par_left . $object->getTitle() . $ref_id] = array(
                            'ref_id' => $ref_id,
                            'obj_id' => $obj_id,
                            'type' => $object->getType(),
                            'title' => $object->getTitle(),
                            'description' => $object->getDescription(),
                            'parent_ref' => $parent_ref_id,
                            'start' => $object->getType() == 'grp' ? $object->getStart() : $object->getCourseStart(),
                            'end' => $object->getType() == 'grp' ? $object->getEnd() : $object->getCourseEnd()
                        );
                    }
                }
            }
        }

        ksort($references);

        return $references;
    }

    public function getItems(array $object_type_white_list = array()) : array
    {
        return $this->getObjectsByMembership($object_type_white_list);
    }
}
