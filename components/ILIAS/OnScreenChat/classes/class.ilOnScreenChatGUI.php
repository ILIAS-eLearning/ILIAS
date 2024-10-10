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

declare(strict_types=1);

use ILIAS\Filesystem\Stream\Streams;
use ILIAS\OnScreenChat\Provider\OnScreenChatProvider;
use ILIAS\OnScreenChat\Repository\Conversation;
use ILIAS\OnScreenChat\Repository\Subscriber;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ilOnScreenChatGUI
 *
 * @author  Thomas Joußen <tjoussen@databay.de>
 * @since   26.07.16
 */
class ilOnScreenChatGUI implements ilCtrlBaseClassInterface
{
    protected static bool $frontend_initialized = false;

    private readonly ILIAS\DI\Container $dic;
    private readonly ILIAS\HTTP\Services $http;
    private readonly ilCtrlInterface $ctrl;
    private readonly ilObjUser $actor;

    public function __construct()
    {
        global $DIC;

        $this->dic = $DIC;
        $this->http = $DIC->http();
        $this->ctrl = $DIC->ctrl();
        $this->actor = $DIC->user();
    }

    private function getResponseWithText(string $body): ResponseInterface
    {
        return $this->dic->http()->response()->withBody(Streams::ofString($body));
    }

    protected static function isOnScreenChatAccessible(ilSetting $chatSettings): bool
    {
        global $DIC;

        return (
            $chatSettings->get('chat_enabled', '0') &&
            $chatSettings->get('enable_osc', '0') &&
            $DIC->user() && !$DIC->user()->isAnonymous()
        );
    }

    public function executeCommand(): void
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case 'getUserProfileData':
                $response = $this->getUserProfileData();
                break;

            case 'verifyLogin':
                $response = $this->verifyLogin();
                break;

            case 'getRenderedConversationItems':
                $provider = new OnScreenChatProvider(
                    $this->dic,
                    new Conversation($this->dic->database(), $this->dic->user()),
                    new Subscriber($this->dic->database(), $this->dic->user())
                );

                $conversationIds = (string) ($this->dic->http()->request()->getQueryParams()['ids'] ?? '');
                $noAggregates = ($this->dic->http()->request()->getQueryParams()['no_aggregates'] ?? '');

                $response = $this->getResponseWithText(
                    $this->dic->ui()->renderer()->renderAsync($provider->getAsyncItem(
                        $conversationIds,
                        $noAggregates !== 'true'
                    ))
                );
                break;

            case 'inviteModal':
                $this->dic->language()->loadLanguageModule('chatroom');
                $txt = $this->dic->language()->txt(...);
                $modal = $this->dic->ui()->factory()->modal()->roundtrip($txt('chat_osc_invite_to_conversation'), $this->dic->ui()->factory()->legacy($txt('chat_osc_search_modal_info')), [
                    $this->dic->ui()->factory()->input()->field()->text($txt('chat_osc_user')),
                ])->withSubmitLabel($txt('confirm'));
                $response = $this->renderAsyncModal('inviteModal', $modal);
                break;

            case 'confirmRemove':
                $this->dic->language()->loadLanguageModule('chatroom');
                $txt = $this->dic->language()->txt(...);
                $modal = $this->dic->ui()->factory()->modal()->interruptive(
                    $txt('chat_osc_leave_grp_conv'),
                    $txt('chat_osc_sure_to_leave_grp_conv'),
                    ''
                )->withActionButtonLabel($txt('confirm'));
                $response = $this->renderAsyncModal('confirmRemove', $modal);
                break;

            case 'getUserlist':
            default:
                $response = $this->getUserList();
        }

        if ($this->ctrl->isAsynch()) {
            $this->http->saveResponse($response);
            $this->http->sendResponse();
            $this->http->close();
        }
    }

    private function verifyLogin(): ResponseInterface
    {
        ilSession::enableWebAccessWithoutSession(true);

        return $this->getResponseWithText(json_encode([
            'loggedIn' => $this->actor->getId() && !$this->actor->isAnonymous()
        ], JSON_THROW_ON_ERROR));
    }

    private function getUserList(): ResponseInterface
    {
        if (!$this->actor->getId() || $this->actor->isAnonymous()) {
            return $this->getResponseWithText(json_encode([], JSON_THROW_ON_ERROR));
        }

        $auto = new ilOnScreenChatUserUserAutoComplete();
        $auto->setUser($this->actor);
        $auto->setPrivacyMode(ilUserAutoComplete::PRIVACY_MODE_RESPECT_USER_SETTING);
        if (isset($this->http->request()->getQueryParams()['fetchall'])) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }
        $auto->setMoreLinkAvailable(true);
        $auto->setSearchFields(['firstname', 'lastname']);
        $auto->setResultField('login');
        $auto->enableFieldSearchableCheck(true);

        return $this->getResponseWithText($auto->getList($this->http->request()->getQueryParams()['term'] ?? ''));
    }

    private function getUserProfileData(): ResponseInterface
    {
        if (!$this->actor->getId() || $this->actor->isAnonymous()) {
            return $this->getResponseWithText(json_encode([], JSON_THROW_ON_ERROR));
        }

        $usrIds = (string) ($this->http->request()->getQueryParams()['usr_ids'] ?? '');
        if ($usrIds === '') {
            return $this->getResponseWithText(json_encode([], JSON_THROW_ON_ERROR));
        }

        $this->dic->language()->loadLanguageModule('user');
        $subscriberRepo = new Subscriber($this->dic->database(), $this->dic->user());
        $data = $subscriberRepo->getDataByUserIds(explode(',', $usrIds));

        ilSession::enableWebAccessWithoutSession(true);

        return $this->getResponseWithText(json_encode($data, JSON_THROW_ON_ERROR));
    }

    public static function initializeFrontend(ilGlobalTemplateInterface $page): void
    {
        global $DIC;

        if (!self::$frontend_initialized) {
            $clientSettings = new ilSetting('chatroom');

            if (!self::isOnScreenChatAccessible($clientSettings)) {
                self::$frontend_initialized = true;
                return;
            }

            $settings = ilChatroomServerSettings::loadDefault();

            $DIC->language()->loadLanguageModule('chatroom');
            $DIC->language()->loadLanguageModule('user');

            $renderer = $DIC->ui()->renderer();
            $factory = $DIC->ui()->factory();

            $chatWindowTemplate = new ilTemplate('tpl.chat-window.html', false, false, 'components/ILIAS/OnScreenChat');
            $chatWindowTemplate->setVariable('SUBMIT_ACTION', $renderer->render(
                $factory->button()->standard($DIC->language()->txt('chat_osc_send'), 'onscreenchat-submit')
            ));
            $chatWindowTemplate->setVariable('ADD_ACTION', $renderer->render(
                $factory->symbol()->glyph()->add('addUser')
            ));
            $chatWindowTemplate->setVariable('MINIMIZE_ACTION', $renderer->render(
                $factory->button()->minimize()
            ));
            $chatWindowTemplate->setVariable('CONVERSATION_ICON', ilUtil::img(ilUtil::getImagePath('standard/icon_pcht.svg')));

            $subscriberRepo = new Subscriber($DIC->database(), $DIC->user());

            $guiConfig = [
                'chatWindowTemplate' => $chatWindowTemplate->get(),
                'messageTemplate' => (new ilTemplate(
                    'tpl.chat-message.html',
                    false,
                    false,
                    'components/ILIAS/OnScreenChat'
                ))->get(),
                'nothingFoundTemplate' => $DIC->ui()->renderer()->render($DIC->ui()->factory()->messageBox()->info($DIC->language()->txt('chat_osc_no_usr_found'))),
                'userId' => $DIC->user()->getId(),
                'username' => $DIC->user()->getLogin(),
                'modalURLTemplate' => ILIAS_HTTP_PATH . '/' . $DIC->ctrl()->getLinkTargetByClass(
                    ilOnScreenChatGUI::class,
                    'postMessage',
                    null,
                    true
                ),
                'userListURL' => ILIAS_HTTP_PATH . '/' . $DIC->ctrl()->getLinkTargetByClass(
                    'ilonscreenchatgui',
                    'getUserList',
                    '',
                    true,
                    false
                ),
                'userProfileDataURL' => $DIC->ctrl()->getLinkTargetByClass(
                    'ilonscreenchatgui',
                    'getUserProfileData',
                    '',
                    true,
                    false
                ),
                'verifyLoginURL' => $DIC->ctrl()->getLinkTargetByClass(
                    'ilonscreenchatgui',
                    'verifyLogin',
                    '',
                    true,
                    false
                ),
                'renderConversationItemsURL' => $DIC->ctrl()->getLinkTargetByClass(
                    'ilonscreenchatgui',
                    'getRenderedConversationItems',
                    '',
                    true,
                    false
                ),
                'loaderImg' => ilUtil::getImagePath('media/loader.svg'),
                'locale' => $DIC->language()->getLangKey(),
                'initialUserData' => $subscriberRepo->getInitialUserProfileData(),
                'enabledBrowserNotifications' => (
                    $clientSettings->get('enable_browser_notifications', '0') &&
                    ilUtil::yn2tf((string) $DIC->user()->getPref('chat_osc_browser_notifications'))
                ),
                'broadcast_typing' => (
                    ilUtil::yn2tf((string) $DIC->user()->getPref('chat_broadcast_typing'))
                ),
                'notificationIconPath' => ilUtil::getImagePath('standard/icon_chta.png'),
            ];

            $chatConfig = [
                'url' => $settings->generateClientUrl() . '/' . $settings->getInstance() . '-im',
                'subDirectory' => $settings->getSubDirectory() . '/socket.io',
                'userId' => $DIC->user()->getId(),
                'username' => $DIC->user()->getLogin(),
            ];

            $DIC->language()->toJS([
                'chat_osc_no_usr_found',
                'chat_osc_write_a_msg',
                'autocomplete_more',
                'chat_osc_minimize',
                'chat_osc_invite_to_conversation',
                'chat_osc_user',
                'chat_osc_add_user',
                'chat_osc_subs_rej_msgs',
                'chat_osc_subs_rej_msgs_p',
                'chat_osc_self_rej_msgs',
                'chat_osc_search_modal_info',
                'chat_osc_head_grp_x_persons',
                'osc_noti_title',
                'chat_osc_conversations',
                'chat_osc_sure_to_leave_grp_conv',
                'chat_osc_user_left_grp_conv',
                'confirm',
                'cancel',
                'chat_osc_leave_grp_conv',
                'chat_osc_no_conv',
                'chat_osc_nc_conv_x_p',
                'chat_osc_nc_conv_x_s',
                'chat_osc_nc_no_conv',
                'chat_user_x_is_typing',
                'chat_users_are_typing',
                'today',
                'yesterday',
            ], $page);

            iljQueryUtil::initjQuery($page);
            iljQueryUtil::initjQueryUI($page);
            ilLinkifyUtil::initLinkify($page);

            $page->addJavaScript('assets/js/modal.js');
            $page->addJavaScript('assets/js/socket.io.min.js');
            $page->addJavaScript('assets/js/Chatroom.min.js');
            $page->addJavaScript('assets/js/jquery.ui.touch-punch.js');
            $page->addJavascript('assets/js/LegacyModal.js');
            $page->addJavascript('assets/js/moment-with-locales.min.js');
            $page->addJavascript('assets/js/browser_notifications.js');
            $page->addJavascript('assets/js/onscreenchat-notifications.js');
            $page->addJavascript('assets/js/moment.js');
            $page->addJavascript('assets/js/socket.io-client/dist/socket.io.js');
            $page->addJavascript('assets/js/chat.js');
            $page->addJavascript('assets/js/onscreenchat.js');
            $page->addOnLoadCode("il.Chat.setConfig(" . json_encode($chatConfig, JSON_THROW_ON_ERROR) . ");");
            $page->addOnLoadCode("il.OnScreenChat.setConfig(" . json_encode($guiConfig, JSON_THROW_ON_ERROR) . ");");
            $page->addOnLoadCode("il.OnScreenChat.init();");
            $page->addOnLoadCode('il.OnScreenChatNotifications.init(' . json_encode([
                'conversationIdleTimeThreshold' => max(
                    1,
                    (int) $clientSettings->get('conversation_idle_state_in_minutes', '1')
                ),
                'logLevel' => $DIC['ilLoggerFactory']->getSettings()->getLevelByComponent('osch'),
            ], JSON_THROW_ON_ERROR) . ');');

            self::$frontend_initialized = true;
        }
    }

    private function renderAsyncModal(string $bus_name, $modal)
    {
        return $this->getResponseWithText($this->dic->ui()->renderer()->renderAsync($modal->withAdditionalOnLoadCode(fn ($id) => (
            'il.OnScreenChat.bus.send(' . json_encode($bus_name, JSON_THROW_ON_ERROR) . ', ' . json_encode([(string) $modal->getShowSignal(), (string) $modal->getCloseSignal()], JSON_THROW_ON_ERROR) . ');'
        ))));
    }
}
