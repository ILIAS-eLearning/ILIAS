<?php declare(strict_types=1);
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\OnScreenChat\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\OnScreenChat\Repository\Conversation;
use ILIAS\OnScreenChat\Repository\Subscriber;
use ILIAS\UI\Component\Symbol\Icon\Standard;

/**
 * Class OnScreenChatProvider
 * @author Ingmar Szmais <iszmais@databay.de>
 */
class OnScreenChatProvider extends AbstractStaticMainMenuProvider
{
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

        $icon = $this->dic->ui()->factory()
                          ->symbol()
                          ->icon()
                          ->standard(Standard::CHTA, $this->dic->language()->txt('public_room'))->withIsOutlined(true);

        return [
            $this->mainmenu->complex($this->if->identifier('mm_chat'))
                           ->withAvailableCallable(function () {
                               $isUser = 0 !== (int) $this->dic->user()->getId() && !$this->dic->user()->isAnonymous();
                               $chatSettings = new \ilSetting('chatroom');
                               $isEnabled = $chatSettings->get('chat_enabled') && $chatSettings->get('enable_osc');
                               return $isUser && $isEnabled;
                           })
                           ->withTitle($this->dic->language()->txt('obj_chtr'))
                           ->withSymbol($icon)
                           ->withContentWrapper(function () {
                               $provider = new OnScreenChatNotificationProvider(
                                   $this->dic,
                                   new Conversation($this->dic->database(), $this->dic->user()),
                                   new Subscriber($this->dic->database(), $this->dic->user())
                               );

                               $conversationIds = (string) ($this->dic->http()->request()->getQueryParams()['ids'] ?? '');
                               $noAggregates = ($this->dic->http()->request()->getQueryParams()['no_aggregates'] ?? '');

                               return $this->dic->ui()->factory()->legacy(
                                   $this->dic->ui()->renderer()->renderAsync(
                                       $provider->getAsyncItem($conversationIds, $noAggregates !== 'true')
                                   )
                               );
                           })
                           ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
                           ->withPosition(25),
        ];
    }
}
