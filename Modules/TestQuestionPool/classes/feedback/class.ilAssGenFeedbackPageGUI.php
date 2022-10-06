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
 * Generic feedback page GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_Calls ilAssGenFeedbackPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilAssGenFeedbackPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilAssGenFeedbackPageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 *
 * @ingroup ModulesTestQuestionPool
 */
class ilAssGenFeedbackPageGUI extends ilPageObjectGUI
{
    /**
     * Constructor
     */
    public function __construct($a_id = 0, $a_old_nr = 0)
    {
        global $DIC;
        $tpl = $DIC['tpl'];

        parent::__construct("qfbg", $a_id, $a_old_nr);
        $this->setTemplateTargetVar('ADM_CONTENT');
        $this->setTemplateOutput(true);
    }
}
