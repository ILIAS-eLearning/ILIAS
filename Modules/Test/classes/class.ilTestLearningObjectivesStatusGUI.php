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
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestLearningObjectivesStatusGUI
{
    private \ILIAS\Test\InternalRequestService $testrequest;
    /**
     * @var ilLanguage
     */
    protected $lng = null;

    /**
     * @var integer
     */
    private $crsObjId = null;

    /**
     * @var integer
     */
    private $usrId = null;

    public function __construct(ilLanguage $lng)
    {
        global $DIC;
        $this->testrequest = $DIC->test()->internal()->request();
        $this->lng = $lng;
    }

    /**
     * @return integer
     */
    public function getCrsObjId(): ?int
    {
        return $this->crsObjId;
    }

    /**
     * @param integer $crsObjId
     */
    public function setCrsObjId($crsObjId)
    {
        $this->crsObjId = $crsObjId;
    }

    /**
     * @return integer
     */
    public function getUsrId(): ?int
    {
        return $this->usrId;
    }

    /**
     * @param integer $usrId
     */
    public function setUsrId($usrId)
    {
        $this->usrId = $usrId;
    }

    public function getHTML($objectiveId = null): string
    {
        $this->lng->loadLanguageModule('crs');

        $tpl = new ilTemplate('tpl.tst_lo_status.html', true, true, 'Modules/Test');

        $tpl->setCurrentBlock('objectives_progress_header');
        $tpl->setVariable('OBJECTIVES_PROGRESS_HEADER', $this->lng->txt($this->getHeaderLangVar($objectiveId)));
        $tpl->parseCurrentBlock();

        $this->renderStatus($tpl, $objectiveId, $this->getUsersObjectivesStatus(
            $this->getCrsObjId(),
            $this->getUsrId()
        ));

        return $tpl->get();
    }

    private function getHeaderLangVar($objectiveId): string
    {
        if ($objectiveId) {
            return 'tst_objective_progress_header';
        }

        return 'tst_objectives_progress_header';
    }

    private function renderStatus($tpl, $objectiveId, $loStatusData)
    {
        $loc_settings = ilLOSettings::getInstanceByObjId($this->getCrsObjId());
        $has_initial_test = (bool) $loc_settings->getInitialTest();

        foreach ($loStatusData as $objtv) {
            if ($objectiveId && $objtv['id'] != $objectiveId) {
                continue;
            }

            $tpl->setCurrentBlock("objective_nolink_bl");
            $tpl->setVariable("OBJECTIVE_NOLINK_TITLE", $objtv["title"]);
            $tpl->parseCurrentBlock();

            $objtv_icon = ilObject::_getIcon($objtv["id"], "small", "lobj");

            $tpl->setCurrentBlock("objective_bl");
            $tpl->setVariable("OBJTV_ICON_URL", $objtv_icon);
            $tpl->setVariable("OBJTV_ICON_ALT", $this->lng->txt("crs_objectives"));

            if ($objtv["type"]) {
                $tpl->setVariable(
                    "LP_OBJTV_PROGRESS",
                    ilContainerObjectiveGUI::buildObjectiveProgressBar($has_initial_test, $objtv["id"], $objtv, true)
                );

                // since ilContainerObjectiveGUI::buildObjectiveProgressBar() "sets an empty ref_id" for ilObjTestGUI,
                // after creating links for different test refs, the "saved ref_id param" for ilObjTestGUI gets overwritten.
                // (!) we need to set an explicit ref_id param for ilObjTestGUI again to keep the things running (!)

                global $DIC; /* @var \ILIAS\DI\Container $DIC */
                $DIC->ctrl()->setParameterByClass('ilObjTestGUI', 'ref_id', $this->testrequest->getRefId());
            }

            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock("objectives_bl");
        $tpl->setVariable("OBJTV_LIST_CRS_ID", $this->getCrsObjId());
        $tpl->parseCurrentBlock();
    }

    private function getUsersObjectivesStatus($crsObjId, $usrId): array
    {
        $res = array();

        $coll_objtv = new ilLPCollectionOfObjectives($crsObjId, ilLPObjSettings::LP_MODE_OBJECTIVES);
        $coll_objtv = $coll_objtv->getItems();
        if ($coll_objtv) {
            // #13373
            $lo_results = $this->getUsersObjectivesResults($crsObjId, $usrId);
            $lo_ass = ilLOTestAssignments::getInstance($crsObjId);

            $tmp = array();
            foreach ($coll_objtv as $objective_id) {
                $title = ilCourseObjective::lookupObjectiveTitle($objective_id, true);

                $tmp[$objective_id] = array(
                    "id" => $objective_id,
                    "title" => $title["title"],
                    "desc" => $title["description"],
                    "itest" => $lo_ass->getTestByObjective($objective_id, ilLOSettings::TYPE_TEST_INITIAL),
                    "qtest" => $lo_ass->getTestByObjective($objective_id, ilLOSettings::TYPE_TEST_QUALIFIED)
                );

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
            foreach ($coll_objtv as $objtv_id) {
                $res[] = $tmp[$objtv_id];
            }
        }

        return $res;
    }

    private function getUsersObjectivesResults($crsObjId, $usrId): array
    {
        $res = array();
        $initial_status = null;
        $lur = new ilLOUserResults($crsObjId, $usrId);

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
