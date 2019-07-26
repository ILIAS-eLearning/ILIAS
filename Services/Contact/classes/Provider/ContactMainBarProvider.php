<?php namespace ILIAS\Contact\Provider;

use ilBuddySystem;
use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Class ContactMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ContactMainBarProvider extends AbstractStaticMainMenuProvider
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
        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_contacts'))
                ->withTitle($this->dic->language()->txt("mm_contacts"))
                ->withAction("ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToContacts")
                ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
                ->withPosition(20)
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                ->withAvailableCallable(
                    function () {
                        return (bool) (ilBuddySystem::getInstance()->isEnabled());
                    }
                ),
        ];
    }
}
