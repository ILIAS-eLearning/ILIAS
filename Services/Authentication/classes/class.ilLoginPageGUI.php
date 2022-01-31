<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
 * Login page GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 *
 * @ilCtrl_Calls ilLoginPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilLoginPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilLoginPageGUI: ilPropertyFormGUI, ilInternalLinkGUI
 *
 * @ingroup ServicesAuthentication
 */
class ilLoginPageGUI extends ilPageObjectGUI
{
    /**
    * Constructor
    */
    public function __construct(int $a_id = 0, int $a_old_nr = 0)
    {
        parent::__construct("auth", $a_id, $a_old_nr);
    }
}
