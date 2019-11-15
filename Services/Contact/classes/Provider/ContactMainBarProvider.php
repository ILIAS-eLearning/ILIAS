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
        $title = $this->dic->language()->txt("mm_contacts");
        //$icon = $this->dic->ui()->factory()->symbol()->icon()->standard("cadm", $title)->withIsOutlined(true);
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/people.svg"), $title);

        return [
            $this->mainmenu->link($this->if->identifier('mm_pd_contacts'))
                ->withTitle($title)
                ->withAction("ilias.php?baseClass=ilDashboardGUI&cmd=jumpToContacts")
                ->withParent(StandardTopItemsProvider::getInstance()->getCommunicationIdentification())
                ->withPosition(20)
	            ->withSymbol($icon)
                ->withNonAvailableReason($this->dic->ui()->factory()->legacy("{$this->dic->language()->txt('component_not_active')}"))
                ->withAvailableCallable(
                    function () {
                        return (bool) (ilBuddySystem::getInstance()->isEnabled());
                    }
                ),
        ];
    }
}
