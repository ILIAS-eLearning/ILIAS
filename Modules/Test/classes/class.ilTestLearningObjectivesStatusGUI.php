<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/Container/classes/class.ilContainerObjectiveGUI.php';

/**
 * @author        Björn Heyser <bheyser@databay.de>
 * @version        $Id$
 *
 * @package     Modules/Test
 */
class ilTestLearningObjectivesStatusGUI
{
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
        $this->lng = $lng;
    }

    /**
     * @return integer
     */
    public function getCrsObjId()
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
    public function getUsrId()
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
    
    public function getHTML($objectiveId = null)
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
    
    private function getHeaderLangVar($objectiveId)
    {
        if ($objectiveId) {
            return 'tst_objective_progress_header';
        }
        
        return 'tst_objectives_progress_header';
    }

    private function renderStatus($tpl, $objectiveId, $loStatusData)
    {
        include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
        $loc_settings = ilLOSettings::getInstanceByObjId($this->getCrsObjId());
        $has_initial_test = (bool) $loc_settings->getInitialTest();

        foreach ($loStatusData as $objtv) {
            if ($objectiveId && $objtv['id'] != $objectiveId) {
                continue;
            }
            
            $tpl->setCurrentBlock("objective_nolink_bl");
            $tpl->setVariable("OBJECTIVE_NOLINK_TITLE", $objtv["title"]);
            $tpl->parseCurrentBlock();

            $objtv_icon = ilUtil::getTypeIconPath("lobj", $objtv["id"]);

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
                $DIC->ctrl()->setParameterByClass('ilObjTestGUI', 'ref_id', (int) $_GET['ref_id']);
            }

            $tpl->parseCurrentBlock();
        }

        $tpl->setCurrentBlock("objectives_bl");
        $tpl->setVariable("OBJTV_LIST_CRS_ID", $this->getCrsObjId());
        $tpl->parseCurrentBlock();
    }
    
    private function getUsersObjectivesStatus($crsObjId, $usrId)
    {
        $res = array();

        // we need the collection for the correct order
        include_once "Services/Tracking/classes/class.ilLPObjSettings.php";
        include_once "Services/Tracking/classes/collection/class.ilLPCollectionOfObjectives.php";
        $coll_objtv = new ilLPCollectionOfObjectives($crsObjId, ilLPObjSettings::LP_MODE_OBJECTIVES);
        $coll_objtv = $coll_objtv->getItems();
        if ($coll_objtv) {
            // #13373
            $lo_results = $this->getUsersObjectivesResults($crsObjId, $usrId);

            include_once "Modules/Course/classes/Objectives/class.ilLOTestAssignments.php";
            $lo_ass = ilLOTestAssignments::getInstance($crsObjId);

            $tmp = array();

            include_once "Modules/Course/classes/class.ilCourseObjective.php";
            foreach ($coll_objtv as $objective_id) {
                // patch optes start

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
                    $tmp[$objective_id]["initial"] = $lo_result["initial"];
                }
            }

            // order
            foreach ($coll_objtv as $objtv_id) {
                $res[] = $tmp[$objtv_id];
            }
        }

        return $res;
    }

    private function getUsersObjectivesResults($crsObjId, $usrId)
    {
        $res = array();

        include_once "Modules/Course/classes/Objectives/class.ilLOUserResults.php";
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
                $result["initial"] = $types[ilLOUserResults::TYPE_INITIAL];
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
