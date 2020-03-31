<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Badge/interfaces/interface.ilBadgeTypeGUI.php";

/**
 * Course LP badge gui
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 *
 * @ingroup ModulesCourse
 */
class ilCourseLPBadgeGUI implements ilBadgeTypeGUI
{
    protected $parent_ref_id; // [int]
    
    public function initConfigForm(ilPropertyFormGUI $a_form, $a_parent_ref_id)
    {
        global $DIC;

        $lng = $DIC['lng'];
        
        $this->parent_ref_id = (int) $a_parent_ref_id;

        $lng->loadLanguageModule("trac");
    
        include_once "Services/Form/classes/class.ilRepositorySelector2InputGUI.php";
        $subitems = new ilRepositorySelector2InputGUI($lng->txt("objects"), "subitems", true);
        
        $exp = $subitems->getExplorerGUI();
        $exp->setSkipRootNode(true);
        $exp->setRootId($this->parent_ref_id);
        $white = $this->getLPTypes($this->parent_ref_id);
        $exp->setSelectableTypes($white);
        if (!in_array("fold", $white)) {
            $white[] = "fold";
        }
        $exp->setTypeWhiteList($white);
        $subitems->setTitleModifier(function ($a_id) {
            $obj_id = ilObject::_lookupObjId($a_id);
            $olp = ilObjectLP::getInstance($obj_id);
            $invalid_modes = ilCourseLPBadgeGUI::getInvalidLPModes();
            $mode = $olp->getModeText($olp->getCurrentMode());
            if (in_array($olp->getCurrentMode(), $invalid_modes)) {
                $mode = "<strong>$mode</strong>";
            }
            return ilObject::_lookupTitle(ilObject::_lookupObjId($a_id)) . " (" . $mode . ")";
        });
        
        $subitems->setRequired(true);
        $a_form->addItem($subitems);
    }
    
    protected function getLPTypes($a_parent_ref_id)
    {
        global $DIC;

        $tree = $DIC['tree'];
            
        $res = array();
                            
        $root = $tree->getNodeData($a_parent_ref_id);
        $sub_items = $tree->getSubTree($root);
        array_shift($sub_items); // remove root
        
        include_once "Services/Object/classes/class.ilObjectLP.php";
        foreach ($sub_items as $node) {
            if (ilObjectLP::isSupportedObjectType($node["type"])) {
                $class = ilObjectLP::getTypeClass($node["type"]);
                $modes = $class::getDefaultModes(ilObjUserTracking::_enabledLearningProgress());
                if (sizeof($modes) > 1) {
                    $res[] = $node["type"];
                }
            }
        }
        
        return $res;
    }
    
    public function importConfigToForm(ilPropertyFormGUI $a_form, array $a_config)
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $lng = $DIC['lng'];
        
        if (is_array($a_config["subitems"])) {
            $items = $a_form->getItemByPostVar("subitems");
            $items->setValue($a_config["subitems"]);
                        
            /*
            include_once "Services/Tracking/classes/class.ilObjUserTracking.php";
            if(!ilObjUserTracking::_enabledLearningProgress())
            {
                $lng->loadLanguageModule("trac");
                $lp = new ilNonEditableValueGUI($lng->txt("tracking_settings"), "", true);
                $a_form->addItem($lp);

                include_once "Services/Object/classes/class.ilObjectLP.php";

                $links = array();
                foreach($a_config["subitems"] as $ref_id)
                {
                    $obj_id = ilObject::_lookupObjId($ref_id);

                    $olp = ilObjectLP::getInstance($obj_id);
                    $mode = $olp->getCurrentMode();

                    $ilCtrl->setParameterByClass("ilLPListOfSettingsGUI", "lpid", $ref_id);
                    $url = $ilCtrl->getLinkTargetByClass("ilLPListOfSettingsGUI", "");
                    $ilCtrl->setParameterByClass("ilLPListOfSettingsGUI", "lpid", "");

                    $links[] = '<p><a href="'.$url.'">'.
                        '<img src="'.ilObject::_getIcon("", "tiny", ilObject::_lookupType($obj_id)).'" /> ' .
                        ilObject::_lookupTitle($obj_id).
                        '</a><div class="help-block">'.$olp->getModeText($mode).'</div>'.
                        '</p>';
                }
                $lp->setValue(implode("\n", $links));
            }
            */
        }
    }
    
    public function getConfigFromForm(ilPropertyFormGUI $a_form)
    {
        return array("subitems" => $a_form->getInput("subitems"));
    }

    /**
     * Get invalid lp modes
     *
     * @param
     * @return
     */
    public static function getInvalidLPModes()
    {
        include_once "Services/Object/classes/class.ilObjectLP.php";
        include_once "Services/Tracking/classes/class.ilLPObjSettings.php";
        include_once "Services/Tracking/classes/class.ilObjUserTracking.php";

        /* supported modes
            LP_MODE_TLT
            LP_MODE_OBJECTIVES
            LP_MODE_TEST_FINISHED
            LP_MODE_TEST_PASSED
            LP_MODE_EXERCISE_RETURNED
            LP_MODE_EVENT
            LP_MODE_SCORM_PACKAGE
            LP_MODE_PLUGIN
            LP_MODE_QUESTIONS
            LP_MODE_SURVEY_FINISHED
            LP_MODE_VISITED_PAGES
            LP_MODE_DOWNLOADED
            LP_MODE_STUDY_PROGRAMME ?!
         */

        $invalid_modes = array(ilLPObjSettings::LP_MODE_DEACTIVATED,
            ilLPObjSettings::LP_MODE_UNDEFINED);

        // without active LP the following modes cannot be supported
        if (!ilObjUserTracking::_enabledLearningProgress()) {
            // status cannot be set without active LP
            $invalid_modes[] = ilLPObjSettings::LP_MODE_MANUAL;
            $invalid_modes[] = ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR;
            $invalid_modes[] = ilLPObjSettings::LP_MODE_COLLECTION_MANUAL;

            // mode cannot be configured without active LP
            $invalid_modes[] = ilLPObjSettings::LP_MODE_COLLECTION;
            $invalid_modes[] = ilLPObjSettings::LP_MODE_COLLECTION_MOBS;
            $invalid_modes[] = ilLPObjSettings::LP_MODE_COLLECTION_TLT;
            $invalid_modes[] = ilLPObjSettings::LP_MODE_SCORM;
            $invalid_modes[] = ilLPObjSettings::LP_MODE_VISITS; // ?
        }
        return $invalid_modes;
    }


    public function validateForm(ilPropertyFormGUI $a_form)
    {
        global $DIC;

        $lng = $DIC['lng'];
        $invalid = array();
        
        include_once "Services/Object/classes/class.ilObjectLP.php";
        include_once "Services/Tracking/classes/class.ilLPObjSettings.php";
        include_once "Services/Tracking/classes/class.ilObjUserTracking.php";

        $invalid_modes = self::getInvalidLPModes();
        
        foreach ($a_form->getInput("subitems") as $ref_id) {
            $obj_id = ilObject::_lookupObjId($ref_id);
            $olp = ilObjectLP::getInstance($obj_id);
            if (in_array($olp->getCurrentMode(), $invalid_modes)) {
                $invalid[] = ilObject::_lookupTitle($obj_id);
            }
        }
        
        if (sizeof($invalid)) {
            $mess = sprintf($lng->txt("badge_course_lp_invalid"), implode(", ", $invalid));
            $a_form->getItemByPostVar("subitems")->setAlert($mess);
            return false;
        }
        
        return true;
    }
}
