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
        $this->tpl->loadStandardTemplate();
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
        $this->tpl->printToStdout();
    }

    // JKN PATCH START
    /**
     * Get footer link
     *
     * @return string footer link
     */
    public static function getFooterLink()
    {
        global $DIC;

        $http = $DIC->http();
        $request_scheme =
            isset($http->request()->getServerParams()['HTTPS'])
            && $http->request()->getServerParams()['HTTPS'] !== 'off'
            ? 'https' : 'http';
        $url = $request_scheme . '://'
            . $http->request()->getServerParams()['HTTP_HOST']
            . $http->request()->getServerParams()['REQUEST_URI'];

        return "mailto:support@cpkn.ca?subject=Support%20Request&body=*%20*%20*%0D%0A" . CLIENT_ID . "%0A" . rawurlencode($url);
    }
    // JKN PATCH END

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
