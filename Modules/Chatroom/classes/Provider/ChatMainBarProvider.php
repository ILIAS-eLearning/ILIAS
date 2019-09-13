<?php declare(strict_types=1);

namespace ILIAS\MainMenu\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;

/**
 * Class ChatMainBarProvider
 * @package ILIAS\MainMenu\Provider
 * @author Michael Jansen <mjansen@databay.de>
 */
class ChatMainBarProvider extends AbstractStaticMainMenuProvider
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
        $dic = $this->dic;

        $publicChatRefId = (int) \ilObjChatroom::_getPublicRefId();
        $publicChatObjId = (int) $dic['ilObjDataCache']->lookupObjId($publicChatRefId);

        return [
            $this->mainmenu->link($this->if->identifier('mm_public_chat'))
                ->withTitle($dic['ilObjDataCache']->lookupTitle($publicChatObjId))
                ->withAction('ilias.php?baseClass=ilRepositoryGUI&cmd=view&ref_id=' . $publicChatRefId)
                ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
                ->withPosition(10)
                ->withSymbol($this->dic->ui()->factory()->symbol()->icon()->standard('chtr', '')->withIsOutlined(true))
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                ->withAvailableCallable(
                    function () use ($publicChatObjId) : bool {
                        return $publicChatObjId > 0;
                    }
                )
                ->withVisibilityCallable(
                    function () use ($dic, $publicChatRefId) : bool {
                        if (0 === (int) $dic->user()->getId() || $dic->user()->isAnonymous()) {
                            return false;
                        }
                        
                        $hasPublicChatRoomAccess = $dic
                            ->rbac()
                            ->system()
                            ->checkAccessOfUser($dic->user()->getId(), 'read', $publicChatRefId);

                        return (
                            (new \ilSetting('chatroom'))->get('chat_enabled') &&
                            $hasPublicChatRoomAccess
                        );
                    }
                ),
        ];
    }
}