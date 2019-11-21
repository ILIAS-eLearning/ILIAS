<?php declare(strict_types=1);

namespace ILIAS\OnScreenChat\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;

/**
 * Class OnScreenChatNotificationProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
class OnScreenChatNotificationProvider extends AbstractNotificationProvider implements NotificationProvider
{
    /**
     * @inheritDoc
     */
    public function getNotifications(): array
    {
        $id = function (string $id): IdentificationInterface {
            return $this->if->identifier($id);
        };

        if (0 === (int)$this->dic->user()->getId() || $this->dic->user()->isAnonymous()) {
            return [];
        }

        $chatSettings = new \ilSetting('chatroom');
        $isEnabled = $chatSettings->get('chat_enabled') && $chatSettings->get('enable_osc');
        if (!$isEnabled) {
            return [];
        }

        $factory = $this->globalScreen()->notifications()->factory();

        $this->dic->language()->loadLanguageModule('chatroom');

        $showAcceptMessageChange = (
            !\ilUtil::yn2tf($this->dic->user()->getPref('chat_osc_accept_msg')) &&
            !(bool)$this->dic->settings()->get('usr_settings_hide_chat_osc_accept_msg', false) &&
            !(bool)$this->dic->settings()->get('usr_settings_disable_chat_osc_accept_msg', false)
        );

        $description = $this->dic->language()->txt('chat_osc_nc_no_conv');
        if ($showAcceptMessageChange) {
            $description = sprintf(
                $this->dic->language()->txt('chat_osc_dont_accept_msg'),
                $this->dic->ctrl()->getLinkTargetByClass(
                    ['ilDashboardGUI', 'ilPersonalSettingsGUI', 'ilPersonalChatSettingsFormGUI'],
                    'showChatOptions'
                )
            );
        }

        $icon = $this->dic->ui()->factory()
            ->symbol()
            ->icon()
            ->standard('chtr', 'conversations');
        $title = $this->dic->language()->txt('chat_osc_conversations');
        if (!$showAcceptMessageChange) {
            $title = $this->dic->language()->txt('chat_osc_conversations');
        }

        $notificationItem = $this->dic->ui()->factory()
            ->item()
            ->notification($title, $icon)
            ->withDescription($description);
        if (!$showAcceptMessageChange) {
            $notificationItem = $notificationItem
                ->withAdditionalOnLoadCode(
                    function($id) {
                        return "
                            il.OnScreenChat.setNotificationItemId('$id');
                        ";
                    }
                );
        }

        $group = $factory
            ->standardGroup($id('chat_bucket_group'))
            ->withTitle('Chat')
            ->addNotification(
                $factory->standard($id('chat_bucket'))
                    ->withNotificationItem($notificationItem)
                    ->withNewAmount(0)
            );

        return [
            $group,
        ];
    }

    /**
     * @param \ILIAS\UI\Component\Item\Notification[] $notificationItems
     */
    private function sendResponseWithNotificationItems(array $notificationItems) : void
    {
        echo $this->dic->ui()->renderer()->renderAsync($notificationItems);
        exit;
    }

    /**
     * Delivers async a new item defined by the data sent through HTTP GET
     */
    public function getAsyncItem() : void
    {
        $noAggregates = (string) ($this->dic->http()->request()->getQueryParams()['no_aggregates'] ?? '');
        $conversationIds = array_filter(explode(',',
            (string) ($this->dic->http()->request()->getQueryParams()['ids'] ?? '')
        ));

        $this->dic->language()->loadLanguageModule('chatroom');

        $icon = $this->dic->ui()->factory()
            ->symbol()
            ->icon()
            ->standard('chtr', 'conversations');
        $title = $this->dic->language()->txt('chat_osc_conversations');
        if ('true' !== $noAggregates && count($conversationIds) > 0) {
            $title = $this->dic->ui()->factory()
                ->link()
                ->standard($title, '#');
        }
        $notificationItem = $this->dic->ui()->factory()
            ->item()
            ->notification($title, $icon)
            ->withDescription($this->dic->language()->txt('chat_osc_nc_no_conv'))
            ->withAdditionalOnLoadCode(
                function($id) {
                    return "
                    il.OnScreenChat.setNotificationItemId('$id');
                ";
                }
            );

        if (0 === count($conversationIds)) {
            $this->sendResponseWithNotificationItems([$notificationItem]);
        }

        if (!$this->dic->user()->getId() || $this->dic->user()->isAnonymous()) {
            $this->sendResponseWithNotificationItems([$notificationItem]);
        }

        /**
         * TODO:
         * - 1. Query conversation from database by the requested ID array
         * - 2. fetch latest message
         * - 3. check(!!!) if user is member of this conv.
         * - 4. Format usernames and message (linkyfy it, and replace smilies)
         */

        if ('true' !== $noAggregates) {
            $aggregatedItems = [];
            foreach ($conversationIds as $conversationId) {
                $name = $conversationId;
                $message = $conversationId;
                
                $aggregateTitle = $this->dic->ui()->factory()
                    ->button()
                    ->shy($name, '') // Important: Do not pass any action here, otherwise there will be onClick/return false;
                    ->withAdditionalOnLoadCode(
                        function($id) use($conversationId) {
                            return "
                                 $('#$id').attr('data-onscreenchat-menu-item', '');
                                 $('#$id').attr('data-onscreenchat-conversation', '$conversationId');
                            ";
                        }
                    );
                $aggregatedItems[] = $this->dic->ui()->factory()
                    ->item()
                    ->notification($aggregateTitle, $icon)
                    ->withDescription($message)
                    ->withAdditionalOnLoadCode(
                        function($id) use($conversationId) {
                            return "
                                $('#$id').find('button.close')
                                    .attr('data-onscreenchat-menu-remove-conversation', '')
                                    .attr('data-onscreenchat-conversation', '$conversationId');
                            ";
                        }
                    )
                    ->withCloseAction('#'); // Important: The # prevents the default onClick handler is triggered
            }
            
            $description = sprintf($this->dic->language()->txt('chat_osc_nc_conv_x_p'), count($aggregatedItems));
            if (1 === count($aggregatedItems)) {
                $description = $this->dic->language()->txt('chat_osc_nc_conv_x_s');
            }

            $notificationItem = $notificationItem
                ->withAggregateNotifications($aggregatedItems)
                ->withDescription($description);
        }

        $this->sendResponseWithNotificationItems([$notificationItem]);
    }
}
