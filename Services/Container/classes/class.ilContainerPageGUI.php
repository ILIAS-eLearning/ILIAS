<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Container page GUI class
 *
 * @author Alexander Killing <killing@leifos.de>
 *
 * @ilCtrl_Calls ilContainerPageGUI: ilPageEditorGUI, ilEditClipboardGUI, ilMDEditorGUI
 * @ilCtrl_Calls ilContainerPageGUI: ilPublicUserProfileGUI, ilNoteGUI
 * @ilCtrl_Calls ilContainerPageGUI: ilPropertyFormGUI, ilInternalLinkGUI, ilPageMultiLangGUI
 */
class ilContainerPageGUI extends ilPageObjectGUI
{
    /**
    * Constructor
    */
    public function __construct($a_id = 0, $a_old_nr = 0, $a_lang = "")
    {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $tpl = $DIC["tpl"];

        parent::__construct("cont", $a_id, $a_old_nr, false, $a_lang);
    }

    /**
     * Get profile back url
     */
    public function getProfileBackUrl()
    {
        $link = ilLink::_getLink((int) $_GET["ref_id"]);
        // make it relative, since profile only accepts relative links as back links
        $link = substr($link, strpos($link, "//") + 2);
        $link = substr($link, strpos($link, "/"));
        return $link;
    }

    public function finishEditing()
    {
        $this->ctrl->returnToParent($this);
    }
}
