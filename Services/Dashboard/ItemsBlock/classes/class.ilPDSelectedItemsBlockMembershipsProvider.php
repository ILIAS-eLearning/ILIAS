<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Membership/classes/class.ilParticipants.php';

/**
 * Class ilPDSelectedItemsBlockMembershipsProvider
 */
class ilPDSelectedItemsBlockMembershipsProvider implements ilPDSelectedItemsBlockProvider
{
    /**
     * @var ilObjUser
     */
    protected $actor;

    /**
     * @var ilTree
     */
    protected $tree;

    /**
     * @var ilAccessHandler
     */
    protected $access;

    /** @var ilObjectDataCache */
    protected $objectDataCache;

    /**
     * ilPDSelectedItemsBlockSelectedItemsProvider constructor.
     * @param ilObjUser $actor
     */
    public function __construct(ilObjUser $actor)
    {
        global $DIC;

        $this->actor = $actor;
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->objectDataCache = $DIC['ilObjDataCache'];
    }

    /**
     * Gets all objects the current user is member of
     * @param array $types
     * @return array array of objects
     */
    protected function getObjectsByMembership($types = array())
    {
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

        $this->objectDataCache->preloadObjectCache($items);
        
        $objIdByType = [];
        foreach ($items as $key => $obj_id) {
            $type = $this->objectDataCache->lookupType($obj_id);
            $objIdByType[$type][] = (int) $obj_id;
        }

        if (isset($objIdByType['grp'])) {
            ilObjGroupAccess::_preloadData($objIdByType['grp'], []);
        } elseif (isset($objIdByType['crs'])) {
            ilObjCourseAccess::_preloadData($objIdByType['crs'], []);
        }

        foreach ($items as $key => $obj_id) {
            $item_references = ilObject::_getAllReferences($obj_id);
            foreach ($item_references as $ref_id) {
                if (!$this->access->checkAccess('read', '', $ref_id) &&
                    !$this->access->checkAccess('visible', '', $ref_id)) {
                     continue;
                }

                if ($this->tree->isInTree($ref_id)) {
                    $parent_ref_id = $this->tree->getParentId($ref_id);
                    $par_left = $this->tree->getLeftValue($parent_ref_id);
                    $par_left = sprintf("%010d", $par_left);
                    $type = $this->objectDataCache->lookupType($obj_id);

                    $periodStart = $periodEnd = null;
                    $periodInfo = [];
                    if ($type === 'crs') {
                        $periodInfo = ilObjCourseAccess::getPeriodInfo()[$obj_id] ?? [];
                    } elseif ($type === 'grp') {
                        $periodInfo = ilObjGroupAccess::getPeriodInfo()[$obj_id] ?? [];
                    }

                    if (!empty($periodInfo) && $periodInfo['start'] !== null && $periodInfo['end'] !== null) {
                        if ($periodInfo['has_time_info']) {
                            $periodStart = new ilDateTime($periodInfo['start']->getTimestamp(), IL_CAL_UNIX);
                            $periodEnd = new ilDateTime($periodInfo['end']->getTimestamp(), IL_CAL_UNIX);
                        } else {
                            $periodStart = new ilDate($periodInfo['start']->format('Y-m-d'), IL_CAL_DATE);
                            $periodEnd = new ilDate($periodInfo['end']->format('Y-m-d'), IL_CAL_DATE);
                        }
                    }

                    if ($parent_ref_id != RECOVERY_FOLDER_ID) {
                        $references[$par_left . $this->objectDataCache->lookupTitle($obj_id) . $ref_id] = array(
                            'ref_id' => $ref_id,
                            'obj_id' => $obj_id,
                            'type' => $type,
                            'title' => $this->objectDataCache->lookupTitle($obj_id),
                            'description' => $this->objectDataCache->lookupDescription($obj_id),
                            'parent_ref' => $parent_ref_id,
                            'start' => $periodStart,
                            'end' => $periodEnd
                        );
                    }
                }
            }
        }

        ksort($references);

        return $references;
    }

    /**
     * @inheritdoc
     */
    public function getItems($object_type_white_list = array())
    {
        return $this->getObjectsByMembership($object_type_white_list);
    }
}
