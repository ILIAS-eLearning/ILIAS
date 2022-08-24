<?php

declare(strict_types=1);

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

namespace ILIAS\OnScreenChat\Provider;

use ilDatePresentation;
use ilDateTime;
use ilDateTimeException;
use ILIAS\DI\Container;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\OnScreenChat\Repository\Conversation;
use ILIAS\OnScreenChat\Repository\Subscriber;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ILIAS\UI\Implementation\Component\Item\Shy;
use ilSetting;
use JsonException;

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
    public function getStaticTopItems(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getStaticSubItems(): array
    {
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(
            Standard::CHTA,
            $this->dic->language()->txt('public_room')
        );

        return [
            $this->mainmenu->complex($this->if->identifier('mm_chat'))
                ->withAvailableCallable(function (): bool {
                    $isUser = 0 !== $this->dic->user()->getId() && !$this->dic->user()->isAnonymous();
                    $chatSettings = new ilSetting('chatroom');
                    $isEnabled = $chatSettings->get('chat_enabled') && $chatSettings->get('enable_osc');
                    return $isUser && $isEnabled;
                })
                ->withTitle($this->dic->language()->txt('mm_private_chats'))
                ->withSymbol($icon)
                ->withContent(
                    $this->dic->ui()->factory()->item()->shy('')->withAdditionalOnLoadCode(
                        static function ($id): string {
                            return "il.OnScreenChat.menuCollector = $id.parentNode;$id.remove();";
                        }
                    )
                )
                ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
                ->withPosition(40)
            ,
        ];
    }

    /**
     * @return Shy[]
     * @throws JsonException
     * @throws ilDateTimeException
     */
    public function getAsyncItem(string $conversationIds, bool $withAggregates): array
    {
        $conversationIds = array_filter(explode(',', $conversationIds));

        if (!$withAggregates || !$this->dic->user()->getId() || $this->dic->user()->isAnonymous()) {
            return [];
        }

        $conversations = $this->conversationRepo->findByIds($conversationIds);

        $allUsrIds = [];
        foreach ($conversations as $conversation) {
            foreach ($conversation->getSubscriberUsrIds() as $id) {
                if ($id !== $this->dic->user()->getId()) {
                    $allUsrIds[$id] = true;
                }
            }
        }
        $allUsrIds = array_keys($allUsrIds);
        $user_data = $this->subscriberRepo->getDataByUserIds($allUsrIds);

        $items = [];
        foreach ($conversations as $conversation) {
            if ($conversation->isGroup()) {
                $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::GCON, 'group-conversation');
            } else {
                $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::CON, 'conversation');
            }

            $users = [];
            foreach ($conversation->getSubscriberUsrIds() as $id) {
                if ($id !== $this->dic->user()->getId()) {
                    $users[] = $user_data[$id]['public_name'];
                }
            }

            $cid = $conversation->getId();

            $items[] = $this->dic->ui()->factory()->item()->shy(implode(', ', $users))
                  ->withDescription($conversation->getLastMessage()->getMessage())
                  ->withProperties(
                      [$this->dic->language()->txt('time') . ':' =>
                          ilDatePresentation::formatDate(
                              new ilDateTime(
                                  (int) ($conversation->getLastMessage()->getCreatedTimestamp() / 1000),
                                  IL_CAL_UNIX
                              )
                          )
                      ]
                  )
                  ->withLeadIcon($icon)
                  ->withClose($this->dic->ui()->factory()->button()->close())
                  ->withAdditionalOnLoadCode(
                      function ($id) use ($cid) {
                          return "il.OnScreenChat.menuCollector.querySelector('#$id').dataset.id = '$cid';";
                      }
                  )
            ;
        }

        return $items;
    }
}
