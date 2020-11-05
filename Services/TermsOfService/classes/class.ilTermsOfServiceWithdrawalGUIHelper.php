<?php

class ilTermsOfServiceWithdrawalGUIHelper
{
    /** @var \ILIAS\DI\Container $DIC */
    protected $DIC;

    /** @var ilLanguage $lng */
    protected $lng;

    /** @var ilCtrl $ctrl */
    protected $ctrl;

    /** @var ilSetting $setting */
    protected $setting;

    /** @var ilTemplate $tpl */
    protected $tpl;

    public function __construct()
    {
        global $DIC;
        $this->DIC = $DIC;
        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->setting = $DIC->settings();
        $this->tpl = $DIC->ui()->mainTemplate();
    }

    public function modifyFooter($footer)
    {
        if (
            !$this->DIC->user()->isAnonymous() &&
            (int) $this->DIC->user()->getId() > 0 &&
            $this->DIC->user()->getAgreeDate()) {
            $helper = new \ilTermsOfServiceHelper();
            $entity = $helper->getCurrentAcceptanceForUser($this->DIC->user());
            if ($entity->getId()) {
                $tos_gui = new \ilObjTermsOfServiceGUI();
                $footer = $footer->withAdditionalModalAndTrigger(
                    $this->DIC->ui()->factory()->modal()->roundtrip(
                        $entity->getTitle(),
                        $this->DIC->ui()->factory()->legacy($entity->getText() . $tos_gui->getWithdrawalSectionForModal()->get())
                    ),
                    $this->DIC->ui()->factory()->button()->shy($this->DIC->language()->txt('usr_agreement'), '#')
                );
            }
        }
        return $footer;
    }

    public function getConsentWithdrawalConfirmation()
    {
        $defaultAuth = AUTH_LOCAL;
        if ($this->setting->get('auth_mode')) {
            $defaultAuth = $this->setting->get('auth_mode');
        }

        $tpl = new \ilTemplate('tpl.withdraw_terms_of_service.html', true, true, 'Services/TermsOfService');
        /** @var ilObjUser $user */
        $user = $GLOBALS['DIC']->user();
        if ( $user->getAuthMode() == AUTH_LDAP
            || ( $user->getAuthMode() == 'default' && $defaultAuth == AUTH_LDAP) )
        {
            $message = nl2br(
                $this->lng->txt('withdrawal_mail_info')
                . $this->lng->txt('withdrawal_mail_text')
            );

            $tpl->setVariable('TERMS_OF_SERVICE_WITHDRAWAL_CONTENT', $message);
            $tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this,'cmd[withdrawAcceptanceLDAP]'));
        } else {
            $tpl->setVariable('TERMS_OF_SERVICE_WITHDRAWAL_CONTENT', $this->lng->txt('withdraw_consent_info'));
            $tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($this,'cmd[withdrawAcceptance]'));
        }

        $tpl->setVariable('WITHDRAW_TERMS_OF_SERVICE', $this->lng->txt('withdraw_usr_agreement'));
        $tpl->setVariable('TXT_WITHDRAW', $this->lng->txt('withdraw'));
        $tpl->setVariable('TXT_CANCEL', $this->lng->txt('cancel'));
        return $tpl->get();
    }
}
