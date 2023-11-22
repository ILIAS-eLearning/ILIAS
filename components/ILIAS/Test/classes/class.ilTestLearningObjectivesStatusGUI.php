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

use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\Test\InternalRequestService;

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package components\ILIAS/Test
 */
class ilTestLearningObjectivesStatusGUI
{
    private ?int $crs_obj_id = null;
    private ?int $usr_id = null;

    public function __construct(
        private ilLanguage $lng,
        private ilCtrl $ctrl,
        private UIFactory $ui_factory,
        private UIRenderer $ui_renderer,
        private InternalRequestService $testrequest
    ) {
    }

    /**
     * @return integer
     */
    public function getCrsObjId(): ?int
    {
        return $this->crs_obj_id;
    }

    /**
     * @param integer $crs_obj_id
     */
    public function setCrsObjId($crs_obj_id)
    {
        $this->crs_obj_id = $crs_obj_id;
    }

    /**
     * @return integer
     */
    public function getUsrId(): ?int
    {
        return $this->usr_id;
    }

    /**
     * @param integer $usr_id
     */
    public function setUsrId($usr_id)
    {
        $this->usr_id = $usr_id;
    }

    public function getHTML(int $objective_id = null): string
    {
        $this->lng->loadLanguageModule('crs');

        $items = $this->buildStatusItems(
            $objective_id,
            $this->getUsersObjectivesStatus(
                $this->getCrsObjId(),
                $this->getUsrId()
            )
        );

        $panel = $this->ui_factory->panel()->standard(
            $this->lng->txt($this->getHeaderLangVar($objective_id)),
            $items
        );

        return $this->ui_renderer->render($panel);
    }

    private function getHeaderLangVar(?int $objective_id): string
    {
        if ($objective_id !== null) {
            return 'tst_objective_progress_header';
        }

        return 'tst_objectives_progress_header';
    }

    /**
     * @param type $lo_status_data
     * @return array
     */
    private function buildStatusItems(?int $objective_id, array $lo_status_data): array
    {
        $items = [];

        foreach ($lo_status_data as $objtv) {
            if ($objective_id !== null && $objtv['id'] !== $objective_id) {
                continue;
            }

            $loc_settings = ilLOSettings::getInstanceByObjId($this->getCrsObjId());
            $compare_value = null;
            if ($objtv["type"] === ilLOUserResults::TYPE_QUALIFIED
                && $loc_settings->getInitialTest() === 1
                && isset($objtv['initial']['result_perc'])) {
                $compare_value = $objtv['initial']['result_perc'];
            }

            $items[] = $this->ui_factory->item()->standard($objtv["title"])
                ->withLeadIcon(
                    $this->ui_factory->symbol()->icon()->custom(
                        ilObject::_getIcon($objtv["id"], "small", "lobj"),
                        $this->lng->txt("crs_objectives")
                    )
                )->withProgress(
                    $this->ui_factory->chart()->progressMeter()->standard(
                        100,
                        $objtv['result_perc'],
                        $objtv['limit_perc'],
                        $compare_value
                    )
                );

            // since ilContainerObjectiveGUI::buildObjectiveProgressBar() "sets an empty ref_id" for ilObjTestGUI,
            // after creating links for different test refs, the "saved ref_id param" for ilObjTestGUI gets overwritten.
            // (!) we need to set an explicit ref_id param for ilObjTestGUI again to keep the things running (!)
            $this->ctrl->setParameterByClass('ilObjTestGUI', 'ref_id', $this->testrequest->getRefId());
        }

        return $items;
    }

    private function getUsersObjectivesStatus($crs_obj_id, $usr_id): array
    {
        $collection_of_objectives = new ilLPCollectionOfObjectives($crs_obj_id, ilLPObjSettings::LP_MODE_OBJECTIVES);
        $objective_items = $collection_of_objectives->getItems();

        if ($objective_items === []) {
            return [];
        }

        $lo_results = $this->getUsersObjectivesResults($crs_obj_id, $usr_id);
        $lo_ass = ilLOTestAssignments::getInstance($crs_obj_id);

        $res = [];
        $tmp = [];
        foreach ($objective_items as $objective_id) {
            $title = ilCourseObjective::lookupObjectiveTitle($objective_id, true);

            $tmp[$objective_id] = [
                "id" => $objective_id,
                "title" => $title["title"],
                "desc" => $title["description"],
                "itest" => $lo_ass->getTestByObjective($objective_id, ilLOSettings::TYPE_TEST_INITIAL),
                "qtest" => $lo_ass->getTestByObjective($objective_id, ilLOSettings::TYPE_TEST_QUALIFIED)
            ];

            // patch optes end

            if (array_key_exists($objective_id, $lo_results)) {
                $lo_result = $lo_results[$objective_id];
                $tmp[$objective_id]["user_id"] = $lo_result["user_id"];
                $tmp[$objective_id]["result_perc"] = $lo_result["result_perc"];
                $tmp[$objective_id]["limit_perc"] = $lo_result["limit_perc"];
                $tmp[$objective_id]["status"] = $lo_result["status"];
                $tmp[$objective_id]["type"] = $lo_result["type"];
                $tmp[$objective_id]["initial"] = $lo_result["initial"] ?? null;
            }
        }

        // order
        foreach ($objective_items as $objtv_id) {
            $res[] = $tmp[$objtv_id];
        }

        return $res;
    }

    private function getUsersObjectivesResults(int $crs_obj_id, int $usr_id): array
    {
        $res = [];
        $initial_status = null;
        $lur = new ilLOUserResults($crs_obj_id, $usr_id);

        foreach ($lur->getCourseResultsForUserPresentation() as $objective_id => $types) {
            // show either initial or qualified for objective
            if (isset($types[ilLOUserResults::TYPE_INITIAL])) {
                $initial_status = $types[ilLOUserResults::TYPE_INITIAL]["status"];
            }

            // qualified test has priority
            if (isset($types[ilLOUserResults::TYPE_QUALIFIED])) {
                $result = $types[ilLOUserResults::TYPE_QUALIFIED];
                $result["type"] = ilLOUserResults::TYPE_QUALIFIED;
                $result["initial"] = $types[ilLOUserResults::TYPE_INITIAL] ?? null;
            } else {
                $result = $types[ilLOUserResults::TYPE_INITIAL];
                $result["type"] = ilLOUserResults::TYPE_INITIAL;
            }

            $result["initial_status"] = $initial_status;

            $res[$objective_id] = $result;
        }

        return $res;
    }
}
