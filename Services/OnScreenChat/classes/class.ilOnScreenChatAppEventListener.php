<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'Services/EventHandling/interfaces/interface.ilAppEventListener.php';

/**
 * Class ilOnScreenChatAppEventListener
 */
class ilOnScreenChatAppEventListener implements ilAppEventListener
{
    /**
     * @inheritdoc
     */
    public static function handleEvent($a_component, $a_event, $a_parameter)
    {
        switch ($a_component) {
            case 'Modules/Chatroom':
                switch ($a_event) {
                    case 'chatSettingsChanged':
                        $GLOBALS['ilLog']->info("Received event: chatSettingsChanged");

                        $message = [
                            $a_parameter['user']->getId() => [
                                'acceptsMessages' => (bool) ilUtil::yn2tf($a_parameter['user']->getPref('chat_osc_accept_msg')),
                            ]
                        ];

                        require_once 'Modules/Chatroom/classes/class.ilChatroomServerConnector.php';
                        require_once 'Modules/Chatroom/classes/class.ilChatroomAdmin.php';

                        $settings = ilChatroomAdmin::getDefaultConfiguration()->getServerSettings();
                        $connector = new ilChatroomServerConnector($settings);
                        $connector->sendUserConfigChange(json_encode($message));
                        break;
                }
                break;
        }
    }
}
