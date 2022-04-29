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
 * Course LP badge gui
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id:$
 * @ingroup ModulesCourse
 */
class ilCourseLPBadgeGUI implements ilBadgeTypeGUI
{
    protected int $parent_ref_id = 0;

    protected ilTree $tree;
    protected ilCtrlInterface $ctrl;
    protected ilLanguage $lng;

    public function __construct()
    {
        global $DIC;

        $this->tree = $DIC->repositoryTree();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->lng->loadLanguageModule('trac');
    }

    public function initConfigForm(ilPropertyFormGUI $a_form, int $a_parent_ref_id) : void
    {
        $this->parent_ref_id = $a_parent_ref_id;

        $subitems = new ilRepositorySelector2InputGUI($this->lng->txt("objects"), "subitems", true);

        $exp = $subitems->getExplorerGUI();
        $exp->setSkipRootNode(true);
        $exp->setRootId($this->parent_ref_id);
        $white = $this->getLPTypes($this->parent_ref_id);
        $exp->setSelectableTypes($white);
        if (!in_array("fold", $white)) {
            $white[] = "fold";
        }
        $exp->setTypeWhiteList($white);
        $subitems->setTitleModifier(function ($a_id) : string {
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

    protected function getLPTypes(int $a_parent_ref_id) : array
    {
        $res = [];
        $root = $this->tree->getNodeData($a_parent_ref_id);
        $sub_items = $this->tree->getSubTree($root);
        array_shift($sub_items); // remove root

        foreach ($sub_items as $node) {
            if (ilObjectLP::isSupportedObjectType($node["type"])) {
                $class = ilObjectLP::getTypeClass($node["type"]);
                /** @noinspection PhpUndefinedMethodInspection */
                $modes = $class::getDefaultModes(ilObjUserTracking::_enabledLearningProgress());
                if (count($modes) > 1) {
                    $res[] = $node["type"];
                }
            }
        }
        return $res;
    }

    public function importConfigToForm(ilPropertyFormGUI $a_form, array $a_config) : void
    {
        if (is_array($a_config["subitems"])) {
            $items = $a_form->getItemByPostVar("subitems");
            $items->setValue($a_config["subitems"]);
        }
    }

    public function getConfigFromForm(ilPropertyFormGUI $a_form) : array
    {
        return ["subitems" => $a_form->getInput("subitems")];
    }

    public static function getInvalidLPModes() : array
    {

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
                               ilLPObjSettings::LP_MODE_UNDEFINED
        );

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

    public function validateForm(ilPropertyFormGUI $a_form) : bool
    {
        $invalid = array();
        $invalid_modes = self::getInvalidLPModes();
        foreach ($a_form->getInput("subitems") as $ref_id) {
            $obj_id = ilObject::_lookupObjId($ref_id);
            $olp = ilObjectLP::getInstance($obj_id);
            if (in_array($olp->getCurrentMode(), $invalid_modes)) {
                $invalid[] = ilObject::_lookupTitle($obj_id);
            }
        }
        if ($invalid !== []) {
            $mess = sprintf($this->lng->txt("badge_course_lp_invalid"), implode(", ", $invalid));
            $a_form->getItemByPostVar("subitems")->setAlert($mess);
            return false;
        }
        return true;
    }
}
