<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageConfig.php");

/**
 * SCORM 2004 page configuration
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesScorm
 */
class ilSCORM2004PageConfig extends ilPageConfig
{
    /**
     * Init
     */
    public function init()
    {
        $this->setEnablePCType("Map", false);
        $this->setEnablePCType("QuestionOverview", true);
        $this->setPreventHTMLUnmasking(false);
        $this->setEnableInternalLinks(true);
        $this->setEnableSelfAssessment(true);
        
        $this->setIntLinkFilterWhiteList(true);
        $this->addIntLinkFilter(array("File"));
        $this->setIntLinkHelpDefaultType("File");
    }
    
    /**
     * Object specific configuration
     *
     * @param int $a_obj_id object id
     */
    public function configureByObjectId($a_obj_id)
    {
        if ($a_obj_id > 0) {
            include_once("./Modules/ScormAicc/classes/class.ilObjSAHSLearningModule.php");
            $this->setLocalizationLanguage(
                ilObjSAHSLearningModule::getAffectiveLocalization($a_obj_id)
            );
            $glo_id = ilObjSAHSLearningModule::lookupAssignedGlossary($a_obj_id);
            if ($glo_id > 0) {
                $this->addIntLinkFilter(array("GlossaryItem"));
            }
        }
    }
}
