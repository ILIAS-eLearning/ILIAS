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
                return "mailto:" . ilLegacyFormElementsUtil::prepareFormOutput(
                    ilSystemSupportContacts::getMailsToAddress()
                );
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
