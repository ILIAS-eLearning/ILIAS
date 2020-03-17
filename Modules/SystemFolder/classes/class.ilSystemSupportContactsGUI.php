<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * System support contacts
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesSystemFolder
 */
class ilSystemSupportContactsGUI
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
        $this->tpl->getStandardTemplate();
        $this->tpl->setTitle($this->lng->txt("adm_support_contacts"));
        include_once("./Services/UIComponent/Panel/classes/class.ilPanelGUI.php");
        $panel = ilPanelGUI::getInstance();
        $panel->setPanelStyle(ilPanelGUI::PANEL_STYLE_PRIMARY);

        $html = "";
        include_once("./Modules/SystemFolder/classes/class.ilSystemSupportContacts.php");
        foreach (ilSystemSupportContacts::getValidSupportContactIds() as $c) {
            include_once("./Services/User/classes/class.ilPublicUserProfileGUI.php");
            $pgui = new ilPublicUserProfileGUI($c);
            //$pgui->setBackUrl($this->ctrl->getLinkTargetByClass("ilinfoscreengui"));
            $pgui->setEmbedded(true);
            $html .= $pgui->getHTML();
        }

        $panel->setBody($html);

        $this->tpl->setContent($panel->getHTML());
        $this->tpl->show();
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
        
        include_once("./Modules/SystemFolder/classes/class.ilSystemSupportContacts.php");

        $users = ilSystemSupportContacts::getValidSupportContactIds();
        if (count($users) > 0) {
            // #17847 - we cannot use a proper GUI on the login screen
            if (!$ilUser->getId()) {
                foreach ($users as $user) {
                    $mail = ilObjUser::_lookupEmail($user);
                    if (trim($mail)) {
                        return "mailto:" . $mail;
                    }
                }
            } else {
                return $ilCtrl->getLinkTargetByClass("ilsystemsupportcontactsgui", "", "", false, false);
            }
        }


        /*$m = ilUtil::prepareFormOutput(ilSystemSupportContacts::getMailToAddress());
        if ($m != "")
        {
            return "mailto:".$m;
        }*/
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
