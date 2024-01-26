<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\OnScreenChat\Provider\OnScreenChatNotificationProvider;
use ILIAS\Filesystem\Stream\Streams;
use ILIAS\OnScreenChat\Repository\Conversation;
use ILIAS\OnScreenChat\Repository\Subscriber;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ilOnScreenChatGUI
 *
 * @author  Thomas Joußen <tjoussen@databay.de>
 * @since   26.07.16
 */
class ilOnScreenChatGUI
{
    /**
     * Boolean to track whether this service has already been initialized.
     * @var bool
     */
    protected static $frontend_initialized = false;

    /** @var \ILIAS\DI\Container */
    private $dic;
    /** @var \ILIAS\DI\HTTPServices */
    private $http;
    /** @var ilCtrl */
    private $ctrl;
    /** @var \ilObjUser */
    private $actor;

    /**
     * ilOnScreenChatGUI constructor.
     */
    public function __construct()
    {
        global $DIC;

        $this->dic = $DIC;
        $this->http = $DIC->http();
        $this->ctrl = $DIC->ctrl();
        $this->actor = $DIC->user();
    }

    /**
     * @param string $body
     * @return ResponseInterface
     */
    private function getResponseWithText(string $body) : ResponseInterface
    {
        return $this->dic->http()->response()->withBody(Streams::ofString($body));
    }

    /**
     * @param ilSetting $chatSettings
     * @return bool
     */
    protected static function isOnScreenChatAccessible(ilSetting $chatSettings) : bool
    {
        global $DIC;

        return $chatSettings->get('chat_enabled') && $chatSettings->get('enable_osc') && $DIC->user() && !$DIC->user()->isAnonymous();
    }

    /**
     * @param ilChatroomServerSettings $chatSettings
     * @return array
     */
    protected static function getEmoticons(ilChatroomServerSettings $chatSettings) : array
    {
        $smileys = array();

        if ($chatSettings->getSmiliesEnabled()) {
            require_once 'Modules/Chatroom/classes/class.ilChatroomSmilies.php';
            ;

            $smileys_array = ilChatroomSmilies::_getSmilies();
            foreach ($smileys_array as $smiley_array) {
                $new_keys = array();
                $new_val = '';
                foreach ($smiley_array as $key => $value) {
                    if ($key == 'smiley_keywords') {
                        $new_keys = explode("\n", $value);
                    }

                    if ($key == 'smiley_fullpath') {
                        $new_val = $value;
                    }
                }

                if (!$new_keys || !$new_val) {
                    continue;
                }

                foreach ($new_keys as $new_key) {
                    $smileys[$new_key] = $new_val;
                }
            }
        }

        return $smileys;
    }

    public function executeCommand() : void
    {
        $cmd = $this->ctrl->getCmd();
        switch ($cmd) {
            case 'getUserProfileData':
                $response = $this->getUserProfileData();
                break;

            case 'verifyLogin':
                $response = $this->verifyLogin();
                break;

            case 'getRenderedNotificationItems':
                $provider = new OnScreenChatNotificationProvider(
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

            case 'getUserlist':
            default:
                $response = $this->getUserList();
        }

        if ($this->ctrl->isAsynch()) {
            $this->http->saveResponse($response);
            $this->http->sendResponse();
            exit();
        }
    }

    /**
     * Checks if a user is logged in. If not, this function should cause an redirect, to disallow chatting while not logged
     * into ILIAS.
     * @return ResponseInterface
     */
    private function verifyLogin() : ResponseInterface
    {
        ilSession::enableWebAccessWithoutSession(true);

        return $this->getResponseWithText(json_encode([
            'loggedIn' => $this->actor->getId() && !$this->actor->isAnonymous()
        ]));
    }

    /**
     * @return ResponseInterface
     */
    private function getUserList() : ResponseInterface
    {
        if (!$this->actor->getId() || $this->actor->isAnonymous()) {
            return $this->getResponseWithText(json_encode([]));
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

    /**
     * @return ResponseInterface
     * @throws ilWACException
     */
    private function getUserProfileData() : ResponseInterface
    {
        if (!$this->actor->getId() || $this->actor->isAnonymous()) {
            return $this->getResponseWithText(json_encode([]));
        }

        $usrIds = (string) ($this->http->request()->getQueryParams()['usr_ids'] ?? '');
        if (0 === strlen($usrIds)) {
            return $this->getResponseWithText(json_encode([]));
        }

        $this->dic->language()->loadLanguageModule('user');
        $subscriberRepo = new Subscriber($this->dic->database(), $this->dic->user());
        $data = $subscriberRepo->getDataByUserIds(explode(',', $usrIds));

        ilSession::enableWebAccessWithoutSession(true);

        return $this->getResponseWithText(json_encode($data));
    }

    /**
     * Initialize frontend and delivers required javascript files and configuration to the global template.
     * @param ilGlobalTemplateInterface $page
     * @throws ilTemplateException
     * @throws ilWACException
     */
    public static function initializeFrontend(ilGlobalTemplateInterface $page) : void
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

            $chatWindowTemplate = new ilTemplate('tpl.chat-window.html', false, false, 'Services/OnScreenChat');
            $chatWindowTemplate->setVariable('SUBMIT_ACTION', $renderer->render(
                $factory->button()->standard($DIC->language()->txt('chat_osc_send'), 'onscreenchat-submit')
            ));
            $chatWindowTemplate->setVariable('ADD_ACTION', $renderer->render(
                $factory->symbol()->glyph()->add('addUser')
            ));
            $chatWindowTemplate->setVariable('CLOSE_ACTION', $renderer->render(
                $factory->button()->close()
            ));
            $chatWindowTemplate->setVariable('CONVERSATION_ICON', ilUtil::img(ilUtil::getImagePath('outlined/icon_pcht.svg')));

            $subscriberRepo = new Subscriber($DIC->database(), $DIC->user());

            $guiConfig = array(
                'chatWindowTemplate' => $chatWindowTemplate->get(),
                'messageTemplate' => (new ilTemplate(
                    'tpl.chat-message.html',
                    false,
                    false,
                    'Services/OnScreenChat'
                ))->get(),
                'modalTemplate' => (new ilTemplate(
                    'tpl.chat-add-user.html',
                    false,
                    false,
                    'Services/OnScreenChat'
                ))->get(),
                'userId' => $DIC->user()->getId(),
                'username' => $DIC->user()->getLogin(),
                'userListURL' => $DIC->ctrl()->getLinkTargetByClass(
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
                'renderNotificationItemsURL' => $DIC->ctrl()->getLinkTargetByClass(
                    'ilonscreenchatgui',
                    'getRenderedNotificationItems',
                    '',
                    true,
                    false
                ),
                'loaderImg' => ilUtil::getImagePath('loader.svg'),
                'emoticons' => self::getEmoticons($settings),
                'locale' => $DIC->language()->getLangKey(),
                'initialUserData' => $subscriberRepo->getInitialUserProfileData(),
                'enabledBrowserNotifications' => (
                    $clientSettings->get('enable_browser_notifications', false) &&
                    (bool) ilUtil::yn2tf($DIC->user()->getPref('chat_osc_browser_notifications'))
                ),
                'notificationIconPath' => \ilUtil::getImagePath('icon_chta.png'),
            );

            $chatConfig = array(
                'url' => $settings->generateClientUrl() . '/' . $settings->getInstance() . '-im',
                'subDirectory' => $settings->getSubDirectory() . '/socket.io',
                'userId' => $DIC->user()->getId(),
                'username' => $DIC->user()->getLogin(),
            );

            $DIC->language()->toJS([
                'chat_osc_no_usr_found',
                'chat_osc_emoticons',
                'chat_osc_write_a_msg',
                'autocomplete_more',
                'close',
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
                'today',
                'yesterday',
            ], $page);

            iljQueryUtil::initjQuery($page);
            iljQueryUtil::initjQueryUI($page);
            ilLinkifyUtil::initLinkify($page);

            $page->addJavaScript('./node_modules/jquery-outside-events/jquery.ba-outside-events.js');
            $page->addJavaScript('./node_modules/@andxor/jquery-ui-touch-punch-fix/jquery.ui.touch-punch.js');
            $page->addJavascript('./Services/UIComponent/Modal/js/Modal.js');
            $page->addJavascript('./node_modules/moment/min/moment-with-locales.min.js');
            $page->addJavascript('./Services/Notifications/js/browser_notifications.js');
            $page->addJavascript('./Services/OnScreenChat/js/onscreenchat-notifications.js');
            $page->addJavascript('./Services/OnScreenChat/js/moment.js');
            $page->addJavascript('./Modules/Chatroom/chat/node_modules/socket.io-client/dist/socket.io.js');
            $page->addJavascript('./Services/OnScreenChat/js/chat.js');
            $page->addJavascript('./Services/OnScreenChat/js/onscreenchat.js');
            $page->addOnLoadCode("il.Chat.setConfig(" . json_encode($chatConfig) . ");");
            $page->addOnLoadCode("il.OnScreenChat.setConfig(" . json_encode($guiConfig) . ");");
            $page->addOnLoadCode("il.OnScreenChat.init();");
            $page->addOnLoadCode('il.OnScreenChatNotifications.init(' . json_encode([
                'conversationIdleTimeThreshold' => max(
                    1,
                    (int) $clientSettings->get('conversation_idle_state_in_minutes', 1)
                ),
                'logLevel' => $DIC['ilLoggerFactory']->getSettings()->getLevelByComponent('osch'),
            ]) . ');');

            self::$frontend_initialized = true;
        }
    }
}
