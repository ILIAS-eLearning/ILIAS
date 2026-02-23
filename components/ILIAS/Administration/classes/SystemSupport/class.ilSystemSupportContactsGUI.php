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

use ILIAS\User\Profile\PublicProfileGUI;
use ILIAS\Data\URI;

/**
 * System support contacts
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @ilCtrl_Calls ilSystemSupportContactsGUI: ILIAS\User\Profile\PublicProfileGUI
 */
class ilSystemSupportContactsGUI implements ilCtrlBaseClassInterface
{
    protected \ILIAS\DI\UIServices $ui;
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
        $this->ui = $DIC->ui();
    }


    /**
     * Execute command
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);

        switch ($next_class) {
            case strtolower(PublicProfileGUI::class):
                $gui = new PublicProfileGUI();
                $this->ctrl->setReturn($this, 'showContacts');
                $this->ctrl->forwardCommand($gui);
                break;

            default:
                $cmd = $this->ctrl->getCmd("showContacts");
                if (in_array($cmd, array("showContacts"))) {
                    $this->$cmd();
                }
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

        $html = "";
        foreach (ilSystemSupportContacts::getValidSupportContactIds() as $c) {
            $pgui = new PublicProfileGUI($c);
            //$pgui->setBackUrl($this->ctrl->getLinkTargetByClass("ilinfoscreengui"));
            $pgui->setEmbedded(true);
            $html .= $pgui->getHTML();
        }

        $f = $this->ui->factory();
        $r = $this->ui->renderer();
        $p = $f->panel()->standard(
            $this->lng->txt("adm_support_contacts"),
            $f->legacy()->content($html)
        );

        $this->tpl->setContent($r->render($p));
        $this->tpl->printToStdout();
    }

    public static function getFooterLink(): null|URI|string
    {
        global $DIC;

        $ilCtrl = $DIC->ctrl();
        $ilUser = $DIC->user();
        $uri = $DIC->http()->request()->getUri();

        $users = ilSystemSupportContacts::getValidSupportContactIds();
        if (count($users) > 0) {
            // #17847 - we cannot use a proper GUI on the login screen
            if (!$ilUser->getId() || $ilUser->getId() == ANONYMOUS_USER_ID) {
                return "mailto:" . ilLegacyFormElementsUtil::prepareFormOutput(
                    ilSystemSupportContacts::getMailsToAddress()
                );
            } else {
                $path = $ilCtrl->getLinkTargetByClass("ilsystemsupportcontactsgui", "", "", false, false);
                return new URI($uri->getScheme() . '://' . $uri->getHost() . '/' . $path);
            }
        }

        return null;
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
