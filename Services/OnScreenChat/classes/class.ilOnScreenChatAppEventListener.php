<?php declare(strict_types=1);

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
                                'acceptsMessages' => ilUtil::yn2tf((string) $a_parameter['user']->getPref('chat_osc_accept_msg')),
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
