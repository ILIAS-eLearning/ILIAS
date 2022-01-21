<?php
/* Copyright (c) 1998-2017 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilOnScreenChatAppEventListener
 */
class ilOnScreenChatAppEventListener implements ilAppEventListener
{
    public static function handleEvent(string $a_component, string $a_event, array $a_parameter) : void
    {
        switch ($a_component) {
            case 'Modules/Chatroom':
                switch ($a_event) {
                    case 'chatSettingsChanged':
                        $GLOBALS['ilLog']->info("Received event: chatSettingsChanged");

                        $message = [
                            $a_parameter['user']->getId() => [
                                'acceptsMessages' => ilUtil::yn2tf($a_parameter['user']->getPref('chat_osc_accept_msg')),
                            ]
                        ];

                        $settings = ilChatroomAdmin::getDefaultConfiguration()->getServerSettings();
                        $connector = new ilChatroomServerConnector($settings);
                        $connector->sendUserConfigChange(json_encode($message, JSON_THROW_ON_ERROR));
                        break;
                }
                break;
        }
    }
}
