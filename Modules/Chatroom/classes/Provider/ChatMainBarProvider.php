<?php declare(strict_types=1);

namespace ILIAS\MainMenu\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ilObjChatroom;
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
            ->standard(Standard::CHTA, $this->dic->language()->txt('public_room'))->withIsOutlined(true);

        return [
            $this->mainmenu->link($this->if->identifier('mm_public_chat'))
                ->withTitle($this->dic->language()->txt('public_room'))
                ->withAction('ilias.php?baseClass=ilRepositoryGUI&cmd=view&ref_id=' . $publicChatRefId)
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
