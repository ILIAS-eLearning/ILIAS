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
    protected ilSetting  $settings;
    private ilPDSelectedItemsBlockMembershipsObjectRepository $repository;

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
     */
    protected function getObjectsByMembership(array $objTypes = []) : array
    {
        $short_desc = $this->settings->get("rep_shorten_description");
        $short_desc_max_length = (int) $this->settings->get("rep_shorten_description_length");

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
            if ($short_desc && $short_desc_max_length !== 0) {
                $description = ilStr::shortenTextExtended($description, $short_desc_max_length, true);
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

    public function getItems(array $object_type_white_list = array()) : array
    {
        return $this->getObjectsByMembership($object_type_white_list);
    }
}
