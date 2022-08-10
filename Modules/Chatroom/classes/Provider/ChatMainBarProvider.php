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

namespace ILIAS\Chatroom\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ilObjChatroom;
use ilObjChatroomGUI;
use ilRepositoryGUI;
use ilSetting;

/**
 * Class ChatMainBarProvider
 * @package ILIAS\MainMenu\Provider
 * @author Michael Jansen <mjansen@databay.de>
 */
class ChatMainBarProvider extends AbstractStaticMainMenuProvider
{
    public function getStaticTopItems() : array
    {
        return [];
    }

    public function getStaticSubItems() : array
    {
        $dic = $this->dic;

        $publicChatRefId = ilObjChatroom::_getPublicRefId();
        $publicChatObjId = (int) $dic['ilObjDataCache']->lookupObjId($publicChatRefId);

        $icon = $this->dic->ui()->factory()
            ->symbol()
            ->icon()
            ->standard(Standard::CHTA, $this->dic->language()->txt('public_room'));

        $this->dic->ctrl()->setParameterByClass(ilObjChatroomGUI::class, 'ref_id', $publicChatRefId);
        $chatUrl = $this->dic->ctrl()->getLinkTargetByClass(
            [
                ilRepositoryGUI::class,
                ilObjChatroomGUI::class
            ],
            'view'
        );

        return [
            $this->mainmenu->link($this->if->identifier('mm_public_chat'))
                ->withTitle($this->dic->language()->txt('public_room'))
                ->withAction($chatUrl)
                ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
                ->withPosition(30)
                ->withSymbol($icon)
                ->withNonAvailableReason(
                    $this->dic->ui()->factory()->legacy($this->dic->language()->txt('component_not_active'))
                )
                ->withAvailableCallable(
                    static function () use ($publicChatObjId) : bool {
                        return $publicChatObjId > 0;
                    }
                )
                ->withVisibilityCallable(
                    static function () use ($dic, $publicChatRefId) : bool {
                        if (0 === $dic->user()->getId() || $dic->user()->isAnonymous()) {
                            return false;
                        }
                        
                        $hasPublicChatRoomAccess = $dic
                            ->rbac()
                            ->system()
                            ->checkAccessOfUser($dic->user()->getId(), 'read', $publicChatRefId);

                        return (
                            (new ilSetting('chatroom'))->get('chat_enabled', '0') &&
                            $hasPublicChatRoomAccess
                        );
                    }
                ),
        ];
    }
}
