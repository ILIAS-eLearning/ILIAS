<?php

use ILIAS\MyStaff\ilMyStaffAccess;

/**
 * Class ilMStShowUserGUI
 * @author            Martin Studer <ms@studer-raimann.ch>
 * @ilCtrl_IsCalledBy ilMStShowUserGUI: ilMyStaffGUI
 * @ilCtrl_Calls      ilMStShowUserGUI: ilUserCertificateGUI
 */
class ilMStShowUserGUI
{
    public const CMD_INDEX = 'index';
    public const CMD_SHOW_USER = 'showUser';
    public const TAB_SHOW_USER = 'show_user';
    public const TAB_SHOW_COURSES = 'show_courses';
    public const TAB_SHOW_CERTIFICATES = 'show_certificates';
    public const TAB_SHOW_COMPETENCES = 'show_competences';

    protected int $usr_id;
    protected ilMyStaffAccess $access;
    private \ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->access = ilMyStaffAccess::getInstance();

        $this->usr_id = $DIC->http()->request()->getQueryParams()['usr_id'];
        $DIC->ctrl()->setParameter($this, 'usr_id', $this->usr_id);

        $DIC->ui()->mainTemplate()->setTitle(ilUserUtil::getNamePresentation($this->usr_id));
        $DIC->ui()->mainTemplate()->setTitleIcon(ilObjUser::_getPersonalPicturePath($this->usr_id, "xxsmall"));
    }

    protected function checkAccessOrFail() : void
    {
        global $DIC;

        if (!$this->usr_id) {
            $this->main_tpl->setOnScreenMessage('failure', $DIC->language()->txt("permission_denied"), true);
            $DIC->ctrl()->redirectByClass(ilDashboardGUI::class, "");
        }

        if ($this->access->hasCurrentUserAccessToMyStaff()
            && $this->access->hasCurrentUserAccessToUser($this->usr_id)) {
            return;
        } else {
            $this->main_tpl->setOnScreenMessage('failure', $DIC->language()->txt("permission_denied"), true);
            $DIC->ctrl()->redirectByClass(ilDashboardGUI::class, "");
        }
    }

    final public function executeCommand() : void
    {
        global $DIC;

        $this->checkAccessOrFail();

        $cmd = $DIC->ctrl()->getCmd();
        $next_class = $DIC->ctrl()->getNextClass();

        switch ($next_class) {
            case strtolower(ilMStShowUserCoursesGUI::class):
                $this->addTabs(self::TAB_SHOW_COURSES);
                $gui = new ilMStShowUserCoursesGUI();
                $DIC->ctrl()->forwardCommand($gui);
                break;
            case strtolower(ilUserCertificateGUI::class):
                $this->addTabs(self::TAB_SHOW_CERTIFICATES);
                $gui = new ilUserCertificateGUI(
                    null,
                    null,
                    null,
                    new ilObjUser($this->usr_id)
                );
                $DIC->ctrl()->forwardCommand($gui);
                break;
            case strtolower(ilMStShowUserCompetencesGUI::class):
                $this->addTabs(self::TAB_SHOW_COMPETENCES);
                $gui = new ilMStShowUserCompetencesGUI($DIC);
                $DIC->ctrl()->forwardCommand($gui);
                break;
            default:

                switch ($cmd) {
                    case self::CMD_SHOW_USER:
                        $this->addTabs(self::TAB_SHOW_USER);
                        $this->$cmd();
                        break;
                    default:
                        $this->index();
                        break;
                }
        }
    }

    protected function index() : void
    {
        global $DIC;
        $DIC->ctrl()->redirectByClass(ilMStShowUserCoursesGUI::class);
    }

    protected function showUser() : void
    {
        global $DIC;

        //Redirect if Profile is not public
        $user = new ilObjUser($this->usr_id);
        if (!$user->hasPublicProfile()) {
            $DIC->ctrl()->redirectByClass(self::class, self::CMD_INDEX);
        }

        $pub_profile = new ilPublicUserProfileGUI($this->usr_id);
        $DIC->ui()->mainTemplate()->setContent($pub_profile->getEmbeddable());
    }

    public function cancel() : void
    {
        global $DIC;

        $DIC->ctrl()->redirect($this);
    }

    protected function addTabs(string $active_tab_id) : void
    {
        global $DIC;

        $DIC->tabs()->setBackTarget($DIC->language()->txt('mst_list_users'), $DIC->ctrl()->getLinkTargetByClass(array(
            ilMyStaffGUI::class,
            self::class,
            ilMStListUsersGUI::class,
        )));

        if ($this->access->hasCurrentUserAccessToMyStaff()) {
            $DIC->tabs()->addTab(self::TAB_SHOW_COURSES, $DIC->language()->txt('mst_list_courses'),
                $DIC->ctrl()->getLinkTargetByClass(array(
                    ilMyStaffGUI::class,
                    self::class,
                    ilMStShowUserCoursesGUI::class,
                )));
        }

        if ($this->access->hasCurrentUserAccessToCertificates()) {
            $DIC->tabs()->addTab(self::TAB_SHOW_CERTIFICATES, $DIC->language()->txt('mst_list_certificates'),
                $DIC->ctrl()->getLinkTargetByClass(array(
                    ilMyStaffGUI::class,
                    self::class,
                    ilUserCertificateGUI::class,
                )));
        }

        if ($this->access->hasCurrentUserAccessToCompetences()) {
            $DIC->tabs()->addTab(self::TAB_SHOW_COMPETENCES, $DIC->language()->txt('mst_list_competences'),
                $DIC->ctrl()->getLinkTargetByClass(array(
                    ilMyStaffGUI::class,
                    self::class,
                    ilMStShowUserCompetencesGUI::class,
                )));
        }

        $user = new ilObjUser($this->usr_id);
        if ($user->hasPublicProfile()) {
            $DIC->ctrl()->setParameterByClass(self::class, 'usr_id', $this->usr_id);
            $public_profile_url = $DIC->ctrl()->getLinkTargetByClass(self::class, self::CMD_SHOW_USER);
            $DIC->tabs()->addTab(self::TAB_SHOW_USER, $DIC->language()->txt('public_profile'), $public_profile_url);
        }

        if ($active_tab_id) {
            $DIC->tabs()->activateTab($active_tab_id);
        }
    }
}
