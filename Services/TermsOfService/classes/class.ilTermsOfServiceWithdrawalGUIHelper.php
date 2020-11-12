<?php declare(strict_types=1);

use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\UI\Factory;
use ILIAS\UI\Renderer;
use Psr\Http\Message\RequestInterface;

/**
 * Class ilTermsOfServiceWithdrawalGUIHelper
 * @author Maximilian Becker <mbecker@databay.de>
 */
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
    protected $uiFactory;
    /** @var Renderer $renderer */
    protected $uiRenderer;

    /**
     * ilTermsOfServiceWithdrawalGUIHelper constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->setting = $DIC->settings();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->user = $DIC->user();
        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();
    }

    /**
     * @return ilTemplate
     */
    private function getWithdrawalSectionForModal() : ilTemplate
    {
        $template = new ilTemplate('tpl.tos_withdrawal_section.html', true, true, 'Services/TermsOfService');
        $template->setVariable('TXT_TOS_WITHDRAWAL_HEADLINE', $this->lng->txt('withdraw_consent_header'));
        $template->setVariable('TXT_TOS_WITHDRAWAL', $this->lng->txt('withdraw_consent_description'));
        $template->setVariable(
            'BTN_TOS_WITHDRAWAL',
            $this->uiRenderer->render(
                $this->uiFactory->button()->standard($this->lng->txt('withdraw_consent'), 'logout.php?withdraw_consent')
            )
        );

        return $template;
    }

    /**
     * @param Footer $footer
     * @return Footer
     * @throws ilTermsOfServiceMissingDatabaseAdapterException
     */
    public function modifyFooter(Footer $footer) : Footer
    {
        if (
            !$this->user->isAnonymous() &&
            (int) $this->user->getId() > 0 &&
            $this->user->getAgreeDate()
        ) {
            $helper = new ilTermsOfServiceHelper();
            $entity = $helper->getCurrentAcceptanceForUser($this->user);
            if ($entity->getId()) {
                $footer = $footer->withAdditionalModalAndTrigger(
                    $this->uiFactory->modal()->roundtrip(
                        $entity->getTitle(),
                        $this->uiFactory->legacy($entity->getText() . $this->getWithdrawalSectionForModal()->get())
                    ),
                    $this->uiFactory->button()->shy($this->lng->txt('usr_agreement'), '#')
                );
            }
        }

        return $footer;
    }

    /**
     * @param object $parentObject
     * @return string
     */
    public function getConsentWithdrawalConfirmation(object $parentObject) : string
    {
        $defaultAuth = AUTH_LOCAL;
        if ($this->setting->get('auth_mode')) {
            $defaultAuth = $this->setting->get('auth_mode');
        }

        $tpl = new ilTemplate('tpl.withdraw_terms_of_service.html', true, true, 'Services/TermsOfService');

        $confirmCommand = 'withdrawAcceptance';
        $cancelCommand = 'cancelWithdrawal';

        if (
            $this->user->getAuthMode() == AUTH_LDAP ||
            ($this->user->getAuthMode() === 'default' && $defaultAuth == AUTH_LDAP)
        ) {
            $message = nl2br(
                $this->lng->txt('withdrawal_mail_info') . $this->lng->txt('withdrawal_mail_text')
            );

            $tpl->setVariable('TERMS_OF_SERVICE_WITHDRAWAL_CONTENT', $message);
        } else {
            $tpl->setVariable('TERMS_OF_SERVICE_WITHDRAWAL_CONTENT', $this->lng->txt('withdraw_consent_info'));
        }
        $tpl->setVariable('FORM_ACTION', $this->ctrl->getFormAction($parentObject, $confirmCommand));
        $tpl->setVariable('CMD_CONFIRM', $confirmCommand);
        $tpl->setVariable('CMD_CANCEL', $cancelCommand);

        $tpl->setVariable('WITHDRAW_TERMS_OF_SERVICE', $this->lng->txt('withdraw_usr_agreement'));
        $tpl->setVariable('TXT_WITHDRAW', $this->lng->txt('withdraw'));
        $tpl->setVariable('TXT_CANCEL', $this->lng->txt('cancel'));

        return $tpl->get();
    }

    /**
     * @param RequestInterface $httpRequest
     */
    public function setWithdrawalInfoForLoginScreen(RequestInterface $httpRequest) : void
    {
        if (isset($httpRequest->getQueryParams()['tos_withdrawal_type'])) {
            $withdrawalType = (int) $httpRequest->getQueryParams()['tos_withdrawal_type'];
            if (1 === $withdrawalType) {
                ilUtil::sendInfo($this->lng->txt('withdrawal_complete_deleted'));
            } elseif (2 === $withdrawalType) {
                ilUtil::sendInfo($this->lng->txt('withdrawal_complete_redirect'));
            } else {
                ilUtil::sendInfo($this->lng->txt('withdrawal_complete'));
            }
        }
    }

    /**
     * @param RequestInterface $httpRequest
     * @return string
     */
    public function getWithdrawalTextForLogoutScreen(RequestInterface $httpRequest) : string
    {
        $withdrawalStatus = ($httpRequest->getQueryParams()['withdrawal_relogin_content'] ?? 0);

        $text = '';
        if ($withdrawalStatus !== 0) {
            $text = $this->uiRenderer->render($this->uiFactory->divider()->horizontal());
            if ($withdrawalStatus === 'internal') {
                $text .= $this->lng->txt('withdraw_consent_description_internal');
            } else {
                $text .= $this->lng->txt('withdraw_consent_description_external');
            }
        }

        return $text;
    }

    /**
     * @param RequestInterface $httpRequest
     * @param ilObjUser $user
     * @param object $guiClass
     */
    public function handleWithdrawalLogoutRequest(
        RequestInterface $httpRequest,
        ilObjUser $user,
        object $guiClass
    ) : void {
        if (!isset($httpRequest->getQueryParams()['withdraw_consent'])) {
            return;
        }

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
            $this->ctrl->setParameter($guiClass, 'withdrawal_relogin_content', 'external');
        } else {
            $this->ctrl->setParameter($guiClass, 'withdrawal_relogin_content', 'internal');
        }
    }
}
