<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\OnScreenChat\Provider;

use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\Notification\Provider\AbstractNotificationProvider;
use ILIAS\GlobalScreen\Scope\Notification\Provider\NotificationProvider;
use ILIAS\OnScreenChat\DTO\ConversationDto;
use ILIAS\OnScreenChat\Repository\Conversation;
use ILIAS\OnScreenChat\Repository\Subscriber;
use ILIAS\UI\Component\Item\Notification;
use ILIAS\UI\Component\Symbol\Icon\Standard;

/**
 * Class OnScreenChatNotificationProvider
 * @author Michael Jansen <mjansen@databay.de>
 */
class OnScreenChatNotificationProvider extends AbstractNotificationProvider implements NotificationProvider
{
    /** @var Conversation */
    private $conversationRepo;
    /** @var Subscriber */
    private $subscriberRepo;

    /**
     * OnScreenChatNotificationProvider constructor.
     * @param Container $dic
     * @param Conversation|null $conversationRepo
     * @param Subscriber|null $subscriberRepo
     */
    public function __construct(Container $dic, Conversation $conversationRepo = null, Subscriber $subscriberRepo = null)
    {
        parent::__construct($dic);
        $dic->language()->loadLanguageModule('chatroom');

        if (null === $conversationRepo) {
            $conversationRepo = new Conversation($dic->database(), $dic->user());
        }
        $this->conversationRepo = $conversationRepo;

        if (null === $subscriberRepo) {
            $subscriberRepo = new Subscriber($dic->database(), $dic->user());
        }
        $this->subscriberRepo = $subscriberRepo;
    }

    /**
     * @inheritDoc
     */
    public function getNotifications() : array
    {
        $id = function (string $id) : IdentificationInterface {
            return $this->if->identifier($id);
        };

        if (0 === (int) $this->dic->user()->getId() || $this->dic->user()->isAnonymous()) {
            return [];
        }

        $chatSettings = new \ilSetting('chatroom');
        $isEnabled = $chatSettings->get('chat_enabled') && $chatSettings->get('enable_osc');
        if (!$isEnabled) {
            return [];
        }

        $factory = $this->globalScreen()->notifications()->factory();

        $showAcceptMessageChange = (
            !\ilUtil::yn2tf($this->dic->user()->getPref('chat_osc_accept_msg')) &&
            !(bool) $this->dic->settings()->get('usr_settings_hide_chat_osc_accept_msg', false) &&
            !(bool) $this->dic->settings()->get('usr_settings_disable_chat_osc_accept_msg', false)
        );

        $description = $this->dic->language()->txt('chat_osc_nc_no_conv');
        if ($showAcceptMessageChange) {
            $description = sprintf(
                $this->dic->language()->txt('chat_osc_dont_accept_msg'),
                $this->dic->ui()->renderer()->render(
                    $this->dic->ui()->factory()
                    ->link()
                    ->standard(
                        $this->dic->language()->txt('chat_osc_dont_accept_msg_link_txt'),
                        $this->dic->ctrl()->getLinkTargetByClass(
                            ['ilDashboardGUI', 'ilPersonalProfileGUI', 'ilUserPrivacySettingsGUI'],
                            'showPrivacySettings'
                        )
                    )
                    ->withOpenInNewViewport(true)
                )
            );
        }

        $icon = $this->dic->ui()->factory()
            ->symbol()
            ->icon()
            ->standard(Standard::CHTA, 'conversations')->withIsOutlined(true);
        $title = $this->dic->language()->txt('chat_osc_conversations');

        $notificationItem = $this->dic->ui()->factory()
            ->item()
            ->notification($title, $icon)
            ->withDescription($description);
        if ($showAcceptMessageChange) {
            /*$notificationItem = $notificationItem->withProperties([
                '' => $this->dic->language()->txt('chat_osc_nc_no_conv')
            ]);*/
        } else {
            $notificationItem = $notificationItem
                ->withAdditionalOnLoadCode(
                    function ($id) {
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
     * @param string $conversationIds
     * @param bool $withAggregates
     * @return Notification[]
     * @throws \ilWACException
     */
    public function getAsyncItem(
        string $conversationIds,
        bool $withAggregates
    ) : array {
        $conversationIds = array_filter(explode(',', $conversationIds));

        $icon = $this->dic->ui()->factory()
                          ->symbol()
                          ->icon()
                          ->standard(Standard::CHTA, 'conversations')->withIsOutlined(true);
        
        $title = $this->dic->language()->txt('chat_osc_conversations');
        if ($withAggregates && count($conversationIds) > 0) {
            $title = $this->dic->ui()->factory()
                ->link()
                ->standard($title, '#');
        }
        $notificationItem = $this->dic->ui()->factory()
            ->item()
            ->notification($title, $icon)
            ->withDescription($this->dic->language()->txt('chat_osc_nc_no_conv'))
            ->withAdditionalOnLoadCode(
                function ($id) {
                    $tsInfo = json_encode(new \stdClass());
                    return "
                        il.OnScreenChat.setConversationMessageTimes($tsInfo);
                        il.OnScreenChat.setNotificationItemId('$id');
                    ";
                }
            );

        if (
            0 === count($conversationIds) ||
            !$withAggregates ||
            (!$this->dic->user()->getId() || $this->dic->user()->isAnonymous())
        ) {
            return [$notificationItem];
        }

        $conversations = $this->conversationRepo->findByIds($conversationIds);
        if (0 === count($conversations)) {
            return [$notificationItem];
        }

        $allUsrIds = [];
        array_walk($conversations, function (ConversationDto $conversation) use (&$allUsrIds) {
            $allUsrIds = array_unique(array_merge($conversation->getSubscriberUsrIds(), $allUsrIds));
        });
        $allUsrData = $this->subscriberRepo->getDataByUserIds($allUsrIds);

        $messageTimesByConversation = [];

        $aggregatedItems = [];
        $latestMessageTimeStamp = null;
        foreach ($conversations as $conversation) {
            $convUsrData = array_filter($allUsrData, function ($key) use ($conversation) {
                return in_array($key, $conversation->getSubscriberUsrIds());
            }, ARRAY_FILTER_USE_KEY);

            $convUsrNames = array_map(function ($value) {
                return $value['public_name'];
            }, $convUsrData);

            $name = implode(', ', $convUsrNames);
            $message = $conversation->getLastMessage()->getMessage();
            $timestamp = (int) ($conversation->getLastMessage()->getCreatedTimestamp() / 1000);
            $formattedDateTime = \ilDatePresentation::formatDate(new \ilDateTime($timestamp, IL_CAL_UNIX));

            $messageTimesByConversation[$conversation->getId()] = [
                'ts' => $conversation->getLastMessage()->getCreatedTimestamp(),
                'formatted' => $formattedDateTime
            ];

            $aggregateTitle = $this->dic->ui()->factory()
                ->button()
                ->shy(
                    $name,
                    ''
                ) // Important: Do not pass any action here, otherwise there will be onClick/return false;
                ->withAdditionalOnLoadCode(
                    function ($id) use ($conversation) {
                        return "
                             $('#$id').attr('data-onscreenchat-menu-item', '');
                             $('#$id').attr('data-onscreenchat-conversation', '{$conversation->getId()}');
                        ";
                    }
                );
            $aggregatedItems[] = $this->dic->ui()->factory()
                ->item()
                ->notification($aggregateTitle, $icon)
                ->withDescription($message)
                ->withAdditionalOnLoadCode(
                    function ($id) use ($conversation) {
                        return "
                            il.OnScreenChat.addConversationToUiIdMapping('{$conversation->getId()}', '$id');

                            $('#$id').find('.il-item-description').html(
                                il.OnScreenChat.getMessageFormatter().format(
                                    $('#$id').find('.il-item-description').html()
                                )                                    
                            );
                            $('#$id').find('button.close')
                                .attr('data-onscreenchat-menu-remove-conversation', '')
                                .attr('data-onscreenchat-conversation', '{$conversation->getId()}');
                        ";
                    }
                )
                ->withProperties([
                    $this->dic->language()->txt('chat_osc_nc_prop_time') => $formattedDateTime,
                ])
                ->withCloseAction('#'); // Important: The # prevents the default onClick handler is triggered

            if ($timestamp > $latestMessageTimeStamp) {
                $latestMessageTimeStamp = $timestamp;
            }
        }

        $description = sprintf($this->dic->language()->txt('chat_osc_nc_conv_x_p'), count($aggregatedItems));
        if (1 === count($aggregatedItems)) {
            $description = $this->dic->language()->txt('chat_osc_nc_conv_x_s');
        }

        $notificationItem = $notificationItem
            ->withAggregateNotifications($aggregatedItems)
            ->withDescription($description)
            ->withAdditionalOnLoadCode(
                function ($id) use ($messageTimesByConversation) {
                    $tsInfo = json_encode($messageTimesByConversation);
                    return "
                        il.OnScreenChat.setConversationMessageTimes($tsInfo);
                    ";
                }
            )
            ->withProperties([
                $this->dic->language()->txt('chat_osc_nc_prop_time') => \ilDatePresentation::formatDate(
                    new \ilDateTime($latestMessageTimeStamp, IL_CAL_UNIX)
                )
            ]);

        return [$notificationItem];
    }
}
