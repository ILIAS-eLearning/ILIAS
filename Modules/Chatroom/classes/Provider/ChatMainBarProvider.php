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

        $publicChatRefId = \ilObjChatroom::_getPublicRefId();

        return [
            $this->mainmenu->link($this->if->identifier('mm_public_chat'))
                ->withTitle($dic['ilObjDataCache']->lookupTitle($dic['ilObjDataCache']->lookupObjId($publicChatRefId)))
                ->withAction('ilias.php?baseClass=ilRepositoryGUI&cmd=view&ref_id=' . $publicChatRefId)
                ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
                ->withPosition(10)
                ->withSymbol($this->dic->ui()->factory()->symbol()->icon()->standard("chtr", "")->withIsOutlined(true))
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                ->withAvailableCallable(
                    function () use ($publicChatRefId, $dic) {
                        if (!$publicChatRefId) {
                            return false;
                        }

                        return (
                            (int) $dic->user()->getId() !== 0 && !$dic->user()->isAnonymous()
                        );
                    }
                )
                ->withVisibilityCallable(
                    function () use ($dic, $publicChatRefId) {
                        $hasPublicChatRoomAccess = $dic
                            ->rbac()
                            ->system()
                            ->checkAccessOfUser($dic->user()->getId(), 'read', $publicChatRefId);

                        return (new \ilSetting('chatroom'))->get('chat_enabled') && $hasPublicChatRoomAccess;
                    }
                ),
        ];
    }
}