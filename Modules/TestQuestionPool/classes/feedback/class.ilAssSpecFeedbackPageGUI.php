<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Modules/TestQuestionPool/classes/feedback/class.ilAssSpecFeedbackPage.php");

/**
 * Specific feedback page GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_Calls ilAssSpecFeedbackPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilAssSpecFeedbackPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilAssSpecFeedbackPageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 *
 * @ingroup ModulesTestQuestionPool
 */
class ilAssSpecFeedbackPageGUI extends ilPageObjectGUI
{
    /**
     * Constructor
     */
    public function __construct($a_id = 0, $a_old_nr = 0)
    {
        global $DIC;
        $tpl = $DIC['tpl'];

        parent::__construct("qfbs", $a_id, $a_old_nr);
        $this->setTemplateTargetVar('ADM_CONTENT');
        $this->setTemplateOutput(true);
    }
}
