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
 * Learning module page configuration
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMPageConfig extends ilPageConfig
{
    public function init() : void
    {
        global $DIC;

        $req = $DIC
            ->learningModule()
            ->internal()
            ->gui()
            ->presentation()
            ->request();

        $lm_set = new ilSetting("lm");
        
        $this->setPreventHTMLUnmasking(false);
        $this->setPreventRteUsage(true);
        $this->setUseAttachedContent(true);
        $this->setIntLinkHelpDefaultType("StructureObject");
        $this->setIntLinkHelpDefaultId($req->getRefId());
        $this->removeIntLinkFilter("File");
        $this->setEnableActivation(true);
        $this->setEnableSelfAssessment(true, false);
        $this->setEnableInternalLinks(true);
        $this->setEnableKeywords(true);
        $this->setEnableInternalLinks(true);
        $this->setEnableAnchors(true);
        $this->setMultiLangSupport(true);
        if ($lm_set->get("time_scheduled_page_activation")) {
            $this->setEnableScheduledActivation(true);
        }

        $mset = new ilSetting("mobs");
        if ($mset->get("mep_activate_pages")) {
            $this->setEnablePCType("ContentInclude", true);
        }
    }

    /**
     * Object specific configuration
     */
    public function configureByObjectId(int $a_obj_id) : void
    {
        if ($a_obj_id > 0) {
            $this->setDisableDefaultQuestionFeedback(ilObjLearningModule::_lookupDisableDefaultFeedback($a_obj_id));
            
            if (ilObjContentObject::isOnlineHelpModule($a_obj_id, true)) {
                $this->setEnableSelfAssessment(false, false);
            }
        }
    }
}
