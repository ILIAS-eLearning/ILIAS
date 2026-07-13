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

        $content = [];
        if (self::isAnonymous() && !self::globalProfilesEnabled()) {
            $mails = [];
            foreach (ilSystemSupportContacts::getValidSupportContactIds() as $user_id) {
                $mail = ilObjUser::_lookupEmail($user_id);
                if ($mail) {
                    $mails[] = $mail;
                }
            }
            $content[] = $this->ui->factory()->listing()->unordered(array_unique($mails));
        } else {
            foreach (ilSystemSupportContacts::getValidSupportContactIds() as $user_id) {
                if (self::isProfileVisible($user_id)) {
                    $pgui = new PublicProfileGUI($user_id);
                    $pgui->setEmbedded(true);
                    $content[] = $this->ui->factory()->legacy()->content($pgui->getHTML());
                }
            }
        }

        $panel = $this->ui->factory()->panel()->standard(
            $this->lng->txt("adm_support_contacts"),
            $content
        );

        $this->tpl->setContent($this->ui->renderer()->render($panel));
        $this->tpl->printToStdout();
    }


    /**
     * Get a contact link to be shown in the footer
     */
    public static function getFooterLink(): ?string
    {
        global $DIC;
        if (!empty(ilSystemSupportContacts::getValidSupportContactIds())) {
            return $DIC->ctrl()->getLinkTargetByClass(self::class);
        }
        return null;
    }

    /**
     * Get the text for a contact link to be shown in the footer
     */
    public static function getFooterText(): string
    {
        global $DIC;
        return $DIC->language()->txt("contact_sysadmin");
    }

    /**
     * Check if the profile of a user can be shown
     * - if it is published for www
     * - OR if it is published for users and the current user is logged in
     */
    public static function isProfileVisible(int $user_id): bool
    {
        $user = new ilObjUser($user_id);
        $public = $user->getPref('public_profile');

        if (self::isAnonymous()) {
            return $public === 'g' && self::globalProfilesEnabled();
        } else {
            return $public === 'g' || $public === 'y';
        }
    }

    /**
     * Check if the current user is anonymous
     */
    private static function isAnonymous(): bool
    {
        global $DIC;
        $current_user_id = $DIC->user()->getId();
        return $current_user_id === 0 || $current_user_id === ANONYMOUS_USER_ID;
    }

    /**
     * Check if user profiles can be shown to anonymous users
     */
    private static function globalProfilesEnabled(): bool
    {
        global $DIC;
        return $DIC->settings()->get('enable_global_profiles') ?? false;
    }
}
