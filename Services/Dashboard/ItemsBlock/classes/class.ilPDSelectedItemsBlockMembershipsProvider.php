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

    /**
     * @var ilSetting
     */
    protected $settings;

    /** @var ilPDSelectedItemsBlockMembershipsObjectDatabaseRepository */
    private $repository;

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
        $this->settings = $DIC->settings();
        $this->repository = new ilPDSelectedItemsBlockMembershipsObjectDatabaseRepository(
            $DIC->database(),
            RECOVERY_FOLDER_ID
        );
    }

    /**
     * Gets all objects the current user is member of
     * @param string[] $objTypes
     * @return array array of objects
     */
    protected function getObjectsByMembership(array $objTypes = []) : array
    {
        $short_desc = $this->settings->get("rep_shorten_description");
        $short_desc_max_length = $this->settings->get("rep_shorten_description_length");

        if (!is_array($objTypes) || $objTypes === []) {
            $objTypes = $this->repository->getValidObjectTypes();
        }

        $references = [];
        foreach ($this->repository->getForUser($this->actor, $objTypes, $this->actor->getLanguage()) as $item) {
            $refId = $item->getRefId();
            $objId = $item->getObjId();
            $parentRefId = $item->getParentRefId();
            $title = $item->getTitle();
            $parentTreeLftValue = $item->getParentLftTree();
            $parentTreeLftValue = sprintf("%010d", $parentTreeLftValue);

            if (!$this->access->checkAccess('visible', '', $refId)) {
                continue;
            }

            $periodStart = $periodEnd = null;
            if ($item->getPeriodStart() !== null && $item->getPeriodEnd() !== null) {
                if ($item->objectPeriodHasTime()) {
                    $periodStart = new ilDateTime($item->getPeriodStart()->getTimestamp(), IL_CAL_UNIX);
                    $periodEnd = new ilDateTime($item->getPeriodEnd()->getTimestamp(), IL_CAL_UNIX);
                } else {
                    $periodStart = new ilDate($item->getPeriodStart()->format('Y-m-d'), IL_CAL_DATE);
                    $periodEnd = new ilDate($item->getPeriodEnd()->format('Y-m-d'), IL_CAL_DATE);
                }
            }

            $description = $item->getDescription();
            if ($short_desc && $short_desc_max_length) {
                $description = ilUtil::shortenText($description, $short_desc_max_length, true);
            }

            $references[$parentTreeLftValue . $title . $refId] = [
                'ref_id' => $refId,
                'obj_id' => $objId,
                'type' => $item->getType(),
                'title' => $title,
                'description' => $description,
                'parent_ref' => $parentRefId,
                'start' => $periodStart,
                'end' => $periodEnd
            ];
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
