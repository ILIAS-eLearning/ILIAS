<?php

declare(strict_types=1);

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

use ILIAS\HTTP\Services;

/**
 * Class ilBuddySystemGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @ilCtrl_isCalledBy ilBuddySystemGUI: ilUIPluginRouterGUI, ilPublicUserProfileGUI
 */
class ilBuddySystemGUI
{
    private const BS_REQUEST_HTTP_GET = 1;
    private const BS_REQUEST_HTTP_POST = 2;

    protected static bool $isFrontendInitialized = false;

    protected ilCtrlInterface $ctrl;
    protected ilBuddyList $buddyList;
    protected ilBuddySystemRelationStateFactory $stateFactory;
    protected ilObjUser $user;
    protected ilLanguage $lng;
    protected Services $http;
    private ilGlobalTemplateInterface $main_tpl;

    public function __construct()
    {
        global $DIC;
        $this->main_tpl = $DIC->ui()->mainTemplate();

        $this->http = $DIC->http();
        $this->ctrl = $DIC['ilCtrl'];
        $this->user = $DIC['ilUser'];
        $this->lng = $DIC['lng'];

        $this->buddyList = ilBuddyList::getInstanceByGlobalUser();
        $this->stateFactory = ilBuddySystemRelationStateFactory::getInstance();

        $this->lng->loadLanguageModule('buddysystem');
    }

    public static function initializeFrontend(ilGlobalTemplateInterface $page): void
    {
        global $DIC;

        if (
            !self::$isFrontendInitialized &&
            ilBuddySystem::getInstance()->isEnabled() &&
            !$DIC->user()->isAnonymous()
        ) {
            $DIC->language()->loadLanguageModule('buddysystem');

            $page->addJavaScript('./Services/Contact/BuddySystem/js/buddy_system.js');

            $config = new stdClass();
            $config->http_post_url = $DIC->ctrl()->getFormActionByClass([
                ilUIPluginRouterGUI::class,
                self::class
            ], '', '', true, false);
            $config->transition_state_cmd = 'transitionAsync';
            $page->addOnLoadCode('il.BuddySystem.setConfig(' . json_encode($config, JSON_THROW_ON_ERROR) . ');');

            $btn_config = new stdClass();
            $btn_config->bnt_class = 'ilBuddySystemLinkWidget';

            $page->addOnLoadCode('il.BuddySystemButton.setConfig(' . json_encode($btn_config, JSON_THROW_ON_ERROR) . ');');
            $page->addOnLoadCode('il.BuddySystemButton.init();');

            self::$isFrontendInitialized = true;
        }
    }

    /**
     * @throws RuntimeException
     */
    public function executeCommand(): void
    {
        if ($this->user->isAnonymous()) {
            throw new RuntimeException('This controller only accepts requests of logged in users');
        }

        $this->{$this->ctrl->getCmd() . 'Command'}();
    }

    protected function isRequestParameterGiven(string $key, int $type): bool
    {
        switch ($type) {
            case self::BS_REQUEST_HTTP_POST:
                $body = $this->http->request()->getParsedBody();
                return (isset($body[$key]) && is_string($body[$key]) && $body[$key] !== '');

            case self::BS_REQUEST_HTTP_GET:
            default:
                $query = $this->http->request()->getQueryParams();
                return (isset($query[$key]) && is_string($query[$key]) && $query[$key] !== '');
        }
    }

    private function requestCommand(): void
    {
        $this->transitionCommand(
            'request',
            'buddy_relation_requested',
            static function (ilBuddySystemRelation $relation): void {
                if (
                    $relation->isUnlinked() &&
                    !ilUtil::yn2tf((string) ilObjUser::_lookupPref($relation->getBuddyUsrId(), 'bs_allow_to_contact_me'))
                ) {
                    throw new ilException('The requested user does not want to get contact requests');
                }
            }
        );
    }

    private function ignoreCommand(): void
    {
        $this->transitionCommand('ignore', 'buddy_request_ignored');
    }

    private function linkCommand(): void
    {
        $this->transitionCommand('link', 'buddy_request_approved');
    }

    private function transitionCommand(
        string $cmd,
        string $positiveFeedbackLanguageId,
        callable $onBeforeExecute = null
    ): void {
        if (!$this->isRequestParameterGiven('user_id', self::BS_REQUEST_HTTP_GET)) {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt('buddy_bs_action_not_possible'), true);
            $this->ctrl->returnToParent($this);
        }

        $usrId = (int) $this->http->request()->getQueryParams()['user_id'];
        try {
            $relation = ilBuddyList::getInstanceByGlobalUser()->getRelationByUserId($usrId);

            if (null !== $onBeforeExecute) {
                $onBeforeExecute($relation);
            }

            ilBuddyList::getInstanceByGlobalUser()->{$cmd}($relation);
            $this->main_tpl->setOnScreenMessage('success', $this->lng->txt($positiveFeedbackLanguageId), true);
        } catch (ilBuddySystemRelationStateAlreadyGivenException | ilBuddySystemRelationStateTransitionException $e) {
            $this->main_tpl->setOnScreenMessage('info', sprintf(
                $this->lng->txt($e->getMessage()),
                ilObjUser::_lookupLogin($usrId)
            ), true);
        } catch (Exception) {
            $this->main_tpl->setOnScreenMessage('info', $this->lng->txt('buddy_bs_action_not_possible'), true);
        }

        $this->redirectToReferer();
    }

    /**
     * Performs a state transition based on the request action
     */
    private function transitionAsyncCommand(): void
    {
        if (!$this->ctrl->isAsynch()) {
            throw new RuntimeException('This action only supports AJAX http requests');
        }

        if (!$this->isRequestParameterGiven('usr_id', self::BS_REQUEST_HTTP_POST)) {
            throw new RuntimeException('Missing "usr_id" parameter');
        }

        if (!$this->isRequestParameterGiven('action', self::BS_REQUEST_HTTP_POST)) {
            throw new RuntimeException('Missing "action" parameter');
        }

        $response = new stdClass();
        $response->success = false;

        try {
            $usr_id = (int) $this->http->request()->getParsedBody()['usr_id'];
            $action = ilUtil::stripSlashes($this->http->request()->getParsedBody()['action']);

            if (ilObjUser::_isAnonymous($usr_id)) {
                throw new ilBuddySystemException(sprintf(
                    'You cannot perform a state transition for the anonymous user (id: %s)',
                    $usr_id
                ));
            }

            $login = ilObjUser::_lookupLogin($usr_id);
            if ($login === '') {
                throw new ilBuddySystemException(sprintf(
                    'You cannot perform a state transition for a non existing user (id: %s)',
                    $usr_id
                ));
            }

            $relation = $this->buddyList->getRelationByUserId($usr_id);

            // The ILIAS JF decided to add a new personal setting
            if (
                $relation->isUnlinked() &&
                !ilUtil::yn2tf((string) ilObjUser::_lookupPref($relation->getBuddyUsrId(), 'bs_allow_to_contact_me'))
            ) {
                throw new ilException('The requested user does not want to get contact requests');
            }

            try {
                $this->buddyList->{$action}($relation);
                $response->success = true;
            } catch (ilBuddySystemRelationStateAlreadyGivenException | ilBuddySystemRelationStateTransitionException $e) {
                $response->message = sprintf($this->lng->txt($e->getMessage()), $login);
            } catch (Exception) {
                $response->message = $this->lng->txt('buddy_bs_action_not_possible');
            }

            $response->state = $relation->getState()::class;
            $response->state_html = $this->stateFactory->getStateButtonRendererByOwnerAndRelation(
                $this->buddyList->getOwnerId(),
                $relation
            )->getHtml();
        } catch (Exception) {
            $response->message = $this->lng->txt('buddy_bs_action_not_possible');
        }

        $this->http->saveResponse(
            $this->http->response()
                ->withAddedHeader('Content-Type', 'application/json')
                ->withBody(ILIAS\Filesystem\Stream\Streams::ofString(json_encode($response, JSON_THROW_ON_ERROR)))
        );
        $this->http->sendResponse();
        $this->http->close();
    }

    private function redirectToReferer(): void
    {
        if (isset($this->http->request()->getServerParams()['HTTP_REFERER'])) {
            $redirectUrl = $this->http->request()->getServerParams()['HTTP_REFERER'];
            $urlParts = parse_url($redirectUrl);

            if (isset($urlParts['path'])) {
                $script = basename($urlParts['path'], '.php');
                if ($script === 'login') {
                    $this->ctrl->returnToParent($this);
                } else {
                    $redirectUrl = ltrim(basename($urlParts['path']), '/');
                    if (isset($urlParts['query'])) {
                        $redirectUrl .= '?' . $urlParts['query'];
                    }
                }
            }

            if ($redirectUrl !== '') {
                $this->ctrl->redirectToURL($redirectUrl);
            }
        }

        $this->ctrl->returnToParent($this);
    }
}
