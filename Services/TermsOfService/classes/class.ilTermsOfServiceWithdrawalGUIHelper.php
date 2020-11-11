<?php

use ILIAS\UI\Factory;
use Psr\Http\Message\RequestInterface;

class ilTermsOfServiceWithdrawalGUIHelper
{
    /** @var ilLanguage $lng */
    protected $lng;

    /** @var ilCtrl $ctrl */
    protected $ctrl;

    /** @var ilSetting $setting */
    protected $setting;

    /** @var ilTemplate $tpl */
    protected $tpl;

    /** @var ilObjUser $user */
    protected $user;

    /** @var Factory $factory */
    protected $factory;

    public function __construct()
    {
        global $DIC;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->setting = $DIC->settings();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->factory = $DIC->ui()->factory();
    }

    public function modifyFooter($footer)
    {
        if (
            !$this->user->isAnonymous() &&
            (int) $this->user->getId() > 0 &&
            $this->user->getAgreeDate()) {
            $helper = new \ilTermsOfServiceHelper();
            $entity = $helper->getCurrentAcceptanceForUser($this->user);
            if ($entity->getId()) {
                $tos_gui = new \ilObjTermsOfServiceGUI();
                $footer = $footer->withAdditionalModalAndTrigger(
                    $this->factory->modal()->roundtrip(
                        $entity->getTitle(),
                        $this->factory->legacy($entity->getText() . $tos_gui->getWithdrawalSectionForModal()->get())
                    ),
                    $this->factory->button()->shy($this->lng->txt('usr_agreement'), '#')
                );
            }
        }
        return $footer;
    }

    public function getConsentWithdrawalConfirmation(ilPersonalProfileGUI $parent_object) : string
    {
        $defaultAuth = AUTH_LOCAL;
        if ($this->setting->get('auth_mode')) {
            $defaultAuth = $this->setting->get('auth_mode');
        }

        $tpl = new \ilTemplate('tpl.withdraw_terms_of_service.html', true, true, 'Services/TermsOfService');
        /** @var ilObjUser $user */
        $user = $GLOBALS['DIC']->user();
        if (
            $user->getAuthMode() == AUTH_LDAP ||
            ($user->getAuthMode() == 'default' && $defaultAuth == AUTH_LDAP)
        ) {
            $message = nl2br(
                $this->lng->txt('withdrawal_mail_info') . $this->lng->txt('withdrawal_mail_text')
            );

            $tpl->setVariable('TERMS_OF_SERVICE_WITHDRAWAL_CONTENT', $message);
            $tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($parent_object, 'cmd[withdrawAcceptanceLDAP]'));
        } else {
            $tpl->setVariable('TERMS_OF_SERVICE_WITHDRAWAL_CONTENT', $this->lng->txt('withdraw_consent_info'));
            $tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($parent_object, 'cmd[withdrawAcceptance]'));
        }

        $tpl->setVariable('WITHDRAW_TERMS_OF_SERVICE', $this->lng->txt('withdraw_usr_agreement'));
        $tpl->setVariable('TXT_WITHDRAW', $this->lng->txt('withdraw'));
        $tpl->setVariable('TXT_CANCEL', $this->lng->txt('cancel'));

        return $tpl->get();
    }

    /**
     * @param RequestInterface $httpRequest
     */
    public function setWithdrawalInfo(RequestInterface $httpRequest) : void
    {
        if (isset($httpRequest->getQueryParams()['wdtdel'])) {
            if ($httpRequest->getQueryParams()['wdtdel'] == 1) {
                ilUtil::sendInfo($GLOBALS['lng']->txt('withdrawal_complete_deleted'));
            } else {
                ilUtil::sendInfo($GLOBALS['lng']->txt('withdrawal_complete_redirect'));
            }
        }
    }

    public function appendWithdrawalText(string $withdrawal_relogin) : string
    {
        $withdrawal_appendage_text = '';
        if ($withdrawal_relogin !== 0) {
            $withdrawal_appendage_text = '<br /><br />';
            if ($withdrawal_relogin == 'internal') {
                $withdrawal_appendage_text .= $GLOBALS['lng']->txt('withdraw_consent_description_internal');
            } else {
                $withdrawal_appendage_text .= $GLOBALS['lng']->txt('withdraw_consent_description_external');
            }
        }
        return $withdrawal_appendage_text;
    }

    public function handleWithdrawalRequest(ilObjUser $user, object $gui_class) : void
    {
        $defaultAuth = AUTH_LOCAL;
        if ($this->setting->get('auth_mode')) {
            $defaultAuth = $this->setting->get('auth_mode');
        }

        $external = false;
        if (
            $user->getAuthMode() == AUTH_PROVIDER_LTI ||
            $user->getAuthMode() == AUTH_ECS ||
            ($user->getAuthMode() === 'default' && $defaultAuth == AUTH_PROVIDER_LTI) ||
            ($user->getAuthMode() === 'default' && $defaultAuth == AUTH_ECS)
        ) {
            $external = true;
        }

        $user->writePref('consent_withdrawal_requested', 1);

        if ($external) {
            $this->ctrl->setParameter($gui_class, 'withdrawal_relogin_content', 'external');
        } else {
            $this->ctrl->setParameter($gui_class, 'withdrawal_relogin_content', 'internal');
        }
    }
}
