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

declare(strict_types=0);

namespace ILIAS\Tracking\View\DataRetrieval;

use ilDateTime;
use ilDBConstants;
use ilDBInterface;
use ILIAS\Tracking\View\DataRetrieval\DataRetrievalInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\ViewInterface;
use ILIAS\Tracking\View\DataRetrieval\FilterInterface;
use ILIAS\Tracking\View\DataRetrieval\Info\FactoryInterface as InfoFactoryInterface;
use ilLPMarks;
use ilLPObjSettings;
use ilObject;
use ilObjectLP;
use ilTrQuery;

class DataRetrieval implements DataRetrievalInterface
{
    protected const KEY_OBJ_ID = "obj_id";
    protected const KEY_USR_ID = "usr_id";
    protected const KEY_OBJ_TITLE = "title";
    protected const KEY_OBJ_DESCRIPTION = "description";
    protected const KEY_OBJ_TYPE = "type";
    protected const KEY_LP_STATUS = "status";
    protected const KEY_LP_MODE = "lp_mode";
    protected const KEY_LP_SPENT_SECONDS = "spent_seconds";
    protected const KEY_LP_VISITS = "visits";
    protected const KEY_LP_READ_COUNT = "read_count";
    protected const KEY_LP_PERCENTAGE = "percentage";
    protected const KEY_LP_STATUS_CHANGED = "status_changed";

    public function __construct(
        protected ilDBInterface $db,
        protected InfoFactoryInterface $info_factory
    ) {
    }

    public function retrieveViewInfo(
        FilterInterface $filter
    ): ViewInterface {
        $object_ids = $filter->getObjectIds();
        $user_ids = $filter->getUserIds();

        $user_obj_id_mappings = [];
        foreach ($user_ids as $usr_id) {
            foreach ($object_ids as $obj_id) {
                $olp = ilObjectLP::getInstance($obj_id);
                $lp_mode = $olp->getCurrentMode();
                $user_obj_id_mappings[$usr_id][$lp_mode][] = $obj_id;
            }
        }

        $data = $this->collectLPDataWithIlTryQuery(
            $user_obj_id_mappings,
            $filter->collectOnlyDataOfObjectsWithLPEnabled()
        );

        $object_infos = [];
        $lp_infos = [];
        $combined_infos = [];
        foreach ($data as $entry) {
            $obj_id = (int) $entry[self::KEY_OBJ_ID];
            $usr_id = (int) $entry[self::KEY_USR_ID];
            $obj_title = (string) $entry[self::KEY_OBJ_TITLE];
            $percentage = (int) $entry[self::KEY_LP_PERCENTAGE];
            $obj_description = ilObject::_lookupDescription($obj_id);
            $lp_status = (int) $entry[self::KEY_LP_STATUS];
            $obj_type = (string) $entry[self::KEY_OBJ_TYPE];
            $lp_mode = (int) $entry[self::KEY_LP_MODE];
            $spent_seconds = (int) $entry[self::KEY_LP_SPENT_SECONDS];
            $status_changed = new ilDateTime($entry[self::KEY_LP_STATUS_CHANGED], IL_CAL_DATETIME);
            $visits = (int) $entry[self::KEY_LP_VISITS];
            $read_count = (int) $entry[self::KEY_LP_READ_COUNT];
            $lp_info = $this->info_factory->lp(
                $usr_id,
                $obj_id,
                $lp_status,
                $percentage,
                $lp_mode,
                $spent_seconds,
                $status_changed,
                $visits,
                $read_count,
                $this->isPercentageAvailable($lp_mode)
            );
            $object_info = $this->info_factory->objectData(
                $obj_id,
                $obj_title,
                $obj_description,
                $obj_type,
            );
            $lp_infos[] = $lp_info;
            $object_infos[] = $object_info;
            $combined_infos[] = $this->info_factory->combined(
                $lp_info,
                $object_info
            );
        }

        return $this->info_factory->view(
            $this->info_factory->iterator()->objectData(...$object_infos),
            $this->info_factory->iterator()->lp(...$lp_infos),
            $this->info_factory->iterator()->combined(...$combined_infos)
        );
    }

    protected function collectLPDataWithIlTryQuery(
        array $user_obj_id_mappings,
        bool $only_data_of_objects_with_lp_enabled = true
    ): array {
        $data = [];
        foreach ($user_obj_id_mappings as $usr_id => $mode_mapping) {
            foreach ($mode_mapping as $lp_mode => $obj_ids) {
                $obj_ids = array_flip($obj_ids);
                $new_data = [];
                switch ($lp_mode) {
                    case ilLPObjSettings::LP_MODE_SCORM:
                        $new_data = ilTrQuery::getSCOsStatusForUser(
                            $usr_id,
                            0,
                            $obj_ids
                        );
                        break;
                    case ilLPObjSettings::LP_MODE_OBJECTIVES:
                        $new_data = ilTrQuery::getObjectivesStatusForUser(
                            $usr_id,
                            0,
                            $obj_ids
                        );
                        break;
                    case ilLPObjSettings::LP_MODE_COLLECTION_MANUAL:
                    case ilLPObjSettings::LP_MODE_COLLECTION_TLT:
                    case ilLPObjSettings::LP_MODE_COLLECTION_MOBS:
                        if ($usr_id) {
                            $data = ilTrQuery::getSubItemsStatusForUser(
                                $usr_id,
                                0,
                                $obj_ids
                            );
                        }
                        break;
                    case ilLPObjSettings::LP_MODE_UNDEFINED:
                    case ilLPObjSettings::LP_MODE_DEACTIVATED:
                        if ($only_data_of_objects_with_lp_enabled) {
                            break;
                        }
                        // no break
                    default:
                        $new_data = ilTrQuery::getObjectsStatusForUser(
                            $usr_id,
                            $obj_ids
                        );
                        break;
                }
                foreach ($new_data as $new) {
                    $new[self::KEY_LP_MODE] = $lp_mode;
                    $new[self::KEY_USR_ID] = $usr_id;
                    $data[] = $new;
                }
            }
        }
        return $data;
    }

    protected function isPercentageAvailable(
        int $lp_mode
    ): bool {
        if (in_array(
            $lp_mode,
            [
                ilLPObjSettings::LP_MODE_TLT,
                ilLPObjSettings::LP_MODE_VISITS,
                ilLPObjSettings::LP_MODE_SCORM,
                ilLPObjSettings::LP_MODE_LTI_OUTCOME,
                ilLPObjSettings::LP_MODE_CMIX_COMPLETED,
                ilLPObjSettings::LP_MODE_CMIX_COMPL_WITH_FAILED,
                ilLPObjSettings::LP_MODE_CMIX_PASSED,
                ilLPObjSettings::LP_MODE_CMIX_PASSED_WITH_FAILED,
                ilLPObjSettings::LP_MODE_CMIX_COMPLETED_OR_PASSED,
                ilLPObjSettings::LP_MODE_CMIX_COMPL_OR_PASSED_WITH_FAILED,
                ilLPObjSettings::LP_MODE_VISITED_PAGES,
                ilLPObjSettings::LP_MODE_TEST_PASSED,
                ilLPObjSettings::LP_MODE_COLLECTION
            ]
        )) {
            return true;
        }
        return false;
    }
}
