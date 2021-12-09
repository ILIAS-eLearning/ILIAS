<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * System support contacts
 *
 * @author Alex Killing <alex.killing@gmx.de>
 */
class ilSystemSupportContactsGUI implements ilCtrlBaseClassInterface
{
    /**
     * @var ilTemplate
     */
    protected $tpl;

    /**
     * @var ilLanguage
     */
    protected $lng;

    protected $ctrl;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $tpl = $DIC["tpl"];
        $lng = $DIC->language();

        $this->ctrl = $ilCtrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
    }


    /**
     * Execute command
     */
    public function executeCommand()
    {
        $cmd = $this->ctrl->getCmd("showContacts");
        if (in_array($cmd, array("showContacts"))) {
            $this->$cmd();
        }
    }

    /**
     * Show contacts
     */
    public function showContacts()
    {
        $this->lng->loadLanguageModule("adm");
        $this->tpl->loadStandardTemplate();
        $this->tpl->setTitle($this->lng->txt("adm_support_contacts"));
        $panel = ilPanelGUI::getInstance();
        $panel->setPanelStyle(ilPanelGUI::PANEL_STYLE_PRIMARY);

        $html = "";
        foreach (ilSystemSupportContacts::getValidSupportContactIds() as $c) {
            $pgui = new ilPublicUserProfileGUI($c);
            //$pgui->setBackUrl($this->ctrl->getLinkTargetByClass("ilinfoscreengui"));
            $pgui->setEmbedded(true);
            $html .= $pgui->getHTML();
        }

        $panel->setBody($html);

        $this->tpl->setContent($panel->getHTML());
        $this->tpl->printToStdout();
    }

    
    /**
     * Get footer link
     *
     * @return string footer link
     */
    public static function getFooterLink()
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $ilUser = $DIC->user();
        
        $users = ilSystemSupportContacts::getValidSupportContactIds();
        if (count($users) > 0) {
            // #17847 - we cannot use a proper GUI on the login screen
            if (!$ilUser->getId() || $ilUser->getId() == ANONYMOUS_USER_ID) {
                return "mailto:" . ilUtil::prepareFormOutput(ilSystemSupportContacts::getMailsToAddress());
            } else {
                return $ilCtrl->getLinkTargetByClass("ilsystemsupportcontactsgui", "", "", false, false);
            }
        }

        return "";
    }

    /**
     * Get footer text
     *
     * @return string footer text
     */
    public static function getFooterText()
    {
        global $DIC;

        $lng = $DIC->language();
        return $lng->txt("contact_sysadmin");
    }
}
