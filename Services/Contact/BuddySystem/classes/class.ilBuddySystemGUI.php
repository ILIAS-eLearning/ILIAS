<?php declare(strict_types=1);

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\DI\HTTPServices;

/**
 * Class ilBuddySystemGUI
 * @author Michael Jansen <mjansen@databay.de>
 * @ilCtrl_isCalledBy ilBuddySystemGUI: ilUIPluginRouterGUI, ilPublicUserProfileGUI
 */
class ilBuddySystemGUI
{
    const BS_REQUEST_HTTP_GET = 1;
    const BS_REQUEST_HTTP_POST = 2;

    /** @var bool */
    protected static $isFrontendInitialized = false;

    /** @var ilCtrl */
    protected $ctrl;

    /** @var ilBuddyList */
    protected $buddyList;

    /** @var ilBuddySystemRelationStateFactory */
    protected $stateFactory;

    /** @var ilObjUser */
    protected $user;

    /** @var ilLanguage */
    protected $lng;

    /** @var HTTPServices */
    protected $http;

    /**
     * ilBuddySystemGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->http = $DIC->http();
        $this->ctrl = $DIC['ilCtrl'];
        $this->user = $DIC['ilUser'];
        $this->lng = $DIC['lng'];

        $this->buddyList = ilBuddyList::getInstanceByGlobalUser();
        $this->stateFactory = ilBuddySystemRelationStateFactory::getInstance();

        $this->lng->loadLanguageModule('buddysystem');
    }

    /**
     * @param ilGlobalTemplateInterface $page
     */
    public static function initializeFrontend(ilGlobalTemplateInterface $page) : void
    {
        global $DIC;

        if (
            ilBuddySystem::getInstance()->isEnabled() &&
            !$DIC->user()->isAnonymous() &&
            !self::$isFrontendInitialized
        ) {
            $DIC->language()->loadLanguageModule('buddysystem');

            $page->addJavascript('./Services/Contact/BuddySystem/js/buddy_system.js');

            $config = new stdClass();
            $config->http_post_url = $DIC->ctrl()->getFormActionByClass([
                'ilUIPluginRouterGUI',
                'ilBuddySystemGUI'
            ], '', '', true, false);
            $config->transition_state_cmd = 'transitionAsync';
            $page->addOnLoadCode("il.BuddySystem.setConfig(" . json_encode($config) . ");");

            $btn_config = new stdClass();
            $btn_config->bnt_class = 'ilBuddySystemLinkWidget';

            $page->addOnLoadCode("il.BuddySystemButton.setConfig(" . json_encode($btn_config) . ");");
            $page->addOnLoadCode("il.BuddySystemButton.init();");

            self::$isFrontendInitialized = true;
        }
    }

    /**
     * @throws RuntimeException
     */
    public function executeCommand() : void
    {
        if ($this->user->isAnonymous()) {
            throw new RuntimeException('This controller only accepts requests of logged in users');
        }

        $nextClass = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch ($nextClass) {
            default:
                $cmd .= 'Command';
                $this->$cmd();
                break;
        }
    }

    /**
     * @param string $key
     * @param int $type
     * @return bool
     */
    protected function isRequestParameterGiven(string $key, int $type) : bool
    {
        switch ($type) {
            case self::BS_REQUEST_HTTP_POST:
                return isset($_POST[$key]) && strlen($_POST[$key]);
                break;

            case self::BS_REQUEST_HTTP_GET:
            default:
                return isset($_GET[$key]) && strlen($_GET[$key]);
                break;
        }
    }

    /**
     *
     */
    private function requestCommand() : void
    {
        $this->transitionCommand('request', 'buddy_relation_requested', function (ilBuddySystemRelation $relation) {
            if (
                $relation->isUnlinked() &&
                !ilUtil::yn2tf(ilObjUser::_lookupPref($relation->getBuddyUsrId(), 'bs_allow_to_contact_me'))
            ) {
                throw new ilException("The requested user does not want to get contact requests");
            }
        });
    }

    /**
     *
     */
    private function ignoreCommand() : void
    {
        $this->transitionCommand('ignore', 'buddy_request_ignored');
    }

    /**
     *
     */
    private function linkCommand() : void
    {
        $this->transitionCommand('link', 'buddy_request_approved');
    }

    /**
     * @param string $cmd
     * @param string $positiveFeedbackLanguageId
     * @param callable|null $onBeforeExecute
     */
    private function transitionCommand(
        string $cmd,
        string $positiveFeedbackLanguageId,
        callable $onBeforeExecute = null
    ) : void {
        if (!$this->isRequestParameterGiven('user_id', self::BS_REQUEST_HTTP_GET)) {
            ilUtil::sendInfo($this->lng->txt('buddy_bs_action_not_possible'), true);
            $this->ctrl->returnToParent($this);
        }

        try {
            $relation = ilBuddyList::getInstanceByGlobalUser()->getRelationByUserId((int) $_GET['user_id']);

            if (null !== $onBeforeExecute) {
                $onBeforeExecute($relation);
            }

            ilBuddyList::getInstanceByGlobalUser()->{$cmd}($relation);
            ilUtil::sendSuccess($this->lng->txt($positiveFeedbackLanguageId), true);
        } catch (ilBuddySystemRelationStateAlreadyGivenException $e) {
            ilUtil::sendInfo(sprintf(
                $this->lng->txt($e->getMessage()),
                ilObjUser::_lookupLogin((int) $_GET['user_id'])
            ), true);
        } catch (ilBuddySystemRelationStateTransitionException $e) {
            ilUtil::sendInfo(sprintf(
                $this->lng->txt($e->getMessage()),
                ilObjUser::_lookupLogin((int) $_GET['user_id'])
            ), true);
        } catch (ilException $e) {
            ilUtil::sendInfo($this->lng->txt('buddy_bs_action_not_possible'), true);
        }

        $this->redirectToReferer();
    }

    /**
     * Performs a state transition based on the request action
     */
    private function transitionAsyncCommand() : void
    {
        if (!$this->ctrl->isAsynch()) {
            throw new RuntimeException('This action only supports AJAX http requests');
        }

        if (!isset($_POST['usr_id']) || !is_numeric($_POST['usr_id'])) {
            throw new RuntimeException('Missing "usr_id" parameter');
        }

        if (!isset($_POST['action']) || !strlen($_POST['action'])) {
            throw new RuntimeException('Missing "action" parameter');
        }

        $response = new stdClass();
        $response->success = false;

        try {
            $usr_id = (int) $_POST['usr_id'];
            $action = ilUtil::stripSlashes($_POST['action']);

            if (ilObjUser::_isAnonymous($usr_id)) {
                throw new ilBuddySystemException(sprintf(
                    "You cannot perform a state transition for the anonymous user (id: %s)",
                    $usr_id
                ));
            }

            if (!strlen(ilObjUser::_lookupLogin($usr_id))) {
                throw new ilBuddySystemException(sprintf(
                    "You cannot perform a state transition for a non existing user (id: %s)",
                    $usr_id
                ));
            }

            $relation = $this->buddyList->getRelationByUserId($usr_id);

            // The ILIAS JF decided to add a new personal setting
            if (
                $relation->isUnlinked() &&
                !ilUtil::yn2tf(ilObjUser::_lookupPref($relation->getBuddyUsrId(), 'bs_allow_to_contact_me'))
            ) {
                throw new ilException("The requested user does not want to get contact requests");
            }

            try {
                $this->buddyList->{$action}($relation);
                $response->success = true;
            } catch (ilBuddySystemRelationStateAlreadyGivenException $e) {
                $response->message = sprintf($this->lng->txt($e->getMessage()), ilObjUser::_lookupLogin((int) $usr_id));
            } catch (ilBuddySystemRelationStateTransitionException $e) {
                $response->message = sprintf($this->lng->txt($e->getMessage()), ilObjUser::_lookupLogin((int) $usr_id));
            } catch (Exception $e) {
                $response->message = $this->lng->txt('buddy_bs_action_not_possible');
            }

            $response->state = get_class($relation->getState());
            $response->state_html = $this->stateFactory->getRendererByOwnerAndRelation(
                $this->buddyList->getOwnerId(),
                $relation
            )->getHtml();
        } catch (Exception $e) {
            $response->message = $this->lng->txt('buddy_bs_action_not_possible');
        }

        echo json_encode($response);
        exit();
    }

    private function redirectToReferer() : void
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
