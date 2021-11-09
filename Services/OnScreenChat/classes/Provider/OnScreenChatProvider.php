<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\OnScreenChat\Provider;

use ilDatePresentation;
use ilDateTime;
use ilDateTimeException;
use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\OnScreenChat\DTO\ConversationDto;
use ILIAS\OnScreenChat\Repository\Conversation;
use ILIAS\OnScreenChat\Repository\Subscriber;
use ILIAS\UI\Component\Item\Notification;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ilSetting;
use JsonException;
use stdClass;

/**
 * Class OnScreenChatProvider
 * @author Ingmar Szmais <iszmais@databay.de>
 */
class OnScreenChatProvider extends AbstractStaticMainMenuProvider
{
    private Conversation $conversationRepo;
    private Subscriber $subscriberRepo;

    public function __construct(
        Container $dic,
        ?Conversation $conversationRepo = null,
        ?Subscriber $subscriberRepo = null
    ) {
        parent::__construct($dic);
        $dic->language()->loadLanguageModule('chatroom');
        $this->conversationRepo = $conversationRepo ?? new Conversation($this->dic->database(), $this->dic->user());
        $this->subscriberRepo = $subscriberRepo ?? new Subscriber($this->dic->database(), $this->dic->user());
    }

    /**
     * @inheritDoc
     */
    public function getStaticTopItems() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getStaticSubItems() : array
    {
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(
            Standard::CHTA,
            $this->dic->language()->txt('public_room')
        )->withIsOutlined(true);

        return [
            $this->mainmenu->complex($this->if->identifier('mm_chat'))
                ->withAvailableCallable(function () {
                    $isUser = 0 !== (int) $this->dic->user()->getId() && !$this->dic->user()->isAnonymous();
                    $chatSettings = new ilSetting('chatroom');
                    $isEnabled = $chatSettings->get('chat_enabled') && $chatSettings->get('enable_osc');
                    return $isUser && $isEnabled;
                })
                ->withTitle($this->dic->language()->txt('obj_chtr'))
                ->withSymbol($icon)
                ->withContentWrapper(function () {
                    $conversationIds = $this->dic->http()->request()->getQueryParams()['ids'] ?? '';
                    $noAggregates = $this->dic->http()->request()->getQueryParams()['no_aggregates'] ?? '';
                    return $this->dic->ui()->factory()->legacy(
                        $this->dic->ui()->renderer()->renderAsync(
                            $this->getAsyncItem($conversationIds, $noAggregates !== 'true')
                        )
                    );
                })
                ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
                ->withPosition(25),
        ];
    }


    /**
     * @param string $conversationIds
     * @param bool   $withAggregates
     *
     * @return Notification[]
     * @throws JsonException
     * @throws ilDateTimeException
     */
    public function getAsyncItem(string $conversationIds, bool $withAggregates) : array
    {
        $conversationIds = array_filter(explode(',', $conversationIds));

        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(
            Standard::CHTA,
            'conversations'
        )->withIsOutlined(true);

        $title = $this->dic->language()->txt('chat_osc_conversations');
        if ($withAggregates && count($conversationIds) > 0) {
            $title = $this->dic->ui()->factory()->link()->standard($title, '#');
        }
        $notificationItem = $this->dic->ui()->factory()->item()->notification($title, $icon)
            ->withDescription($this->dic->language()->txt('chat_osc_nc_no_conv'))
            ->withAdditionalOnLoadCode(
                function ($id) {
                    $tsInfo = json_encode(new stdClass());
                    return "
                        il.OnScreenChat.setConversationMessageTimes($tsInfo);
                        il.OnScreenChat.setNotificationItemId('$id');
                    ";
                }
            );

        if (!$withAggregates ||
            0 === count($conversationIds) ||
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
            $formattedDateTime = ilDatePresentation::formatDate(new ilDateTime($timestamp, IL_CAL_UNIX));

            $messageTimesByConversation[$conversation->getId()] = [
                'ts' => $conversation->getLastMessage()->getCreatedTimestamp(),
                'formatted' => $formattedDateTime
            ];

            $aggregateTitle = $this->dic->ui()->factory()->button()->shy($name, '')
                ->withAdditionalOnLoadCode(
                    function ($id) use ($conversation) {
                        return "
                             $('#$id').attr('data-onscreenchat-menu-item', '');
                             $('#$id').attr('data-onscreenchat-conversation', '{$conversation->getId()}');
                        ";
                    }
                );
            $aggregatedItems[] = $this->dic->ui()->factory()->item()->notification($aggregateTitle, $icon)
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
                            $('#$id').find('button.close').attr('data-onscreenchat-menu-remove-conversation','').attr('data-onscreenchat-conversation', '{$conversation->getId()}');
                        ";
                    }
                )
                ->withProperties([$this->dic->language()->txt('chat_osc_nc_prop_time') => $formattedDateTime])
                ->withCloseAction('#');
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
                    return "il.OnScreenChat.setConversationMessageTimes($tsInfo);";
                }
            )
            ->withProperties([
                $this->dic->language()->txt('chat_osc_nc_prop_time') => ilDatePresentation::formatDate(
                    new ilDateTime($latestMessageTimeStamp, IL_CAL_UNIX)
                )
            ]);

        return [$notificationItem];
    }
}
