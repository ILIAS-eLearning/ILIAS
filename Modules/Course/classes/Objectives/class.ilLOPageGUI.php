<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/COPage/classes/class.ilPageObjectGUI.php");
include_once("./Modules/Course/classes/Objectives/class.ilLOPageGUI.php");

/**
 * (Course) learning objective page GUI class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 *
 * @ilCtrl_Calls ilLOPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilLOPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilLOPageGUI: ilPropertyFormGUI, ilInternalLinkGUI, ilPageMultiLangGUI
 *
 * @ingroup ModulesCourse
 */
class ilLOPageGUI extends ilPageObjectGUI
{
    /**
    * Constructor
    */
    public function __construct($a_id = 0, $a_old_nr = 0, $a_lang = "")
    {
        parent::__construct("lobj", $a_id, $a_old_nr, false, $a_lang);
    }
}
