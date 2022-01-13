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
    /** @var ilTermsOfServiceHelper */
    protected $tosHelper;

    /**
     * ilTermsOfServiceWithdrawalGUIHelper constructor.
     * @param ilObjUser $subjectUser
     */
    public function __construct(ilObjUser $subjectUser)
    {
        global $DIC;

        $this->user = $subjectUser;

        $this->lng = $DIC->language();
        $this->ctrl = $DIC->ctrl();
        $this->setting = $DIC->settings();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->uiFactory = $DIC->ui()->factory();
        $this->uiRenderer = $DIC->ui()->renderer();
        $this->tosHelper = new ilTermsOfServiceHelper();
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
            $this->tosHelper->isGloballyEnabled() &&
            $this->tosHelper->isIncludedUser($this->user) &&
            $this->user->getAgreeDate()
        ) {
            $entity = $this->tosHelper->getCurrentAcceptanceForUser($this->user);
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
     * @param RequestInterface $httpRequest
     * @param object $guiClass
     */
    public function handleWithdrawalLogoutRequest(
        RequestInterface $httpRequest,
        object $guiClass
    ) : void {
        if (!isset($httpRequest->getQueryParams()['withdraw_consent'])) {
            return;
        }

        if (!$this->tosHelper->isGloballyEnabled() || !$this->tosHelper->isIncludedUser($this->user)) {
            return;
        }

        $defaultAuth = AUTH_LOCAL;
        if ($this->setting->get('auth_mode')) {
            $defaultAuth = $this->setting->get('auth_mode');
        }

        $external = false;
        if (
            $this->user->getAuthMode() == AUTH_PROVIDER_LTI ||
            $this->user->getAuthMode() == AUTH_ECS ||
            ($this->user->getAuthMode() === 'default' && $defaultAuth == AUTH_PROVIDER_LTI) ||
            ($this->user->getAuthMode() === 'default' && $defaultAuth == AUTH_ECS)
        ) {
            $external = true;
        }

        $this->user->writePref('consent_withdrawal_requested', 1);

        if ($external) {
            $this->ctrl->setParameter($guiClass, 'withdrawal_relogin_content', 'external');
        } else {
            $this->ctrl->setParameter($guiClass, 'withdrawal_relogin_content', 'internal');
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
     * @param object $parentObject
     * @return string
     */
    public function getConsentWithdrawalConfirmation(object $parentObject) : string
    {
        $defaultAuth = AUTH_LOCAL;
        if ($this->setting->get('auth_mode')) {
            $defaultAuth = $this->setting->get('auth_mode');
        }

        $isLdapUser = (
            $this->user->getAuthMode() == AUTH_LDAP ||
            ($this->user->getAuthMode() === 'default' && $defaultAuth == AUTH_LDAP)
        );

        $lng_suffix = ''; 
        if (!$this->user->getAgreeDate()) {
            $lng_suffix = '_no_consent_yet';
        }
        $question = $this->lng->txt('withdrawal_sure_account' . $lng_suffix);
        if (!$isLdapUser && (bool) $this->setting->get('tos_withdrawal_usr_deletion', false))  {
            $question = $this->lng->txt('withdrawal_sure_account_deletion' . $lng_suffix);
        }

        $confirmation = $this->uiFactory->messageBox()->confirmation($question)->withButtons([
            $this->uiFactory->button()->standard(
                $this->lng->txt('confirm'),
                $this->ctrl->getFormAction($parentObject, 'withdrawAcceptance')
            ),
            $this->uiFactory->button()->standard(
                $this->lng->txt('cancel'),
                $this->ctrl->getFormAction($parentObject, 'cancelWithdrawal')
            ),
        ]);

        if ($isLdapUser) {
            $message = nl2br(str_ireplace("[BR]", "\n", sprintf(
                $this->lng->txt('withdrawal_mail_info') . $this->lng->txt('withdrawal_mail_text'),
                $this->user->getFullname(),
                $this->user->getLogin(),
                $this->user->getExternalAccount()
            )));

            $panelContent = $this->uiFactory->legacy(
                $this->uiRenderer->render([
                    $confirmation,
                    $this->uiFactory->divider()->horizontal(),
                    $this->uiFactory->legacy($message)
                ])
            );

            $content = $this->uiRenderer->render(
                $this->uiFactory->panel()->standard($this->lng->txt('withdraw_usr_agreement'), $panelContent)
            );
        } else {
            $content = $this->uiRenderer->render($confirmation);
        }

        return $content;
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
}
