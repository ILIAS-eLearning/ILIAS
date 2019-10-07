<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\NotificationCenter;
use ILIAS\UI\Component\Component;

/**
 * Class NotificationCenterRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class NotificationCenterRenderer implements MetaBarItemRenderer
{

    use isSupportedTrait;
    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    private $ui;
    /**
     * @var \ILIAS\GlobalScreen\Services
     */
    private $gs;


    /**
     * BaseMetaBarItemRenderer constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->ui = $DIC->ui();
        $this->gs = $DIC->globalScreen();
    }


    /**
     * @param NotificationCenter $item
     *
     * @return Component
     */
    public function getComponentForItem(isItem $item) : Component
    {
        $f = $this->ui->factory();

        $combined = $f->mainControls()->slate()->combined("Notification Center", $item->getSymbol());

        foreach ($this->gs->collector()->notifications()->getNotifications() as $notification) {
            $component = $notification->getRenderer()->getComponentForItem($notification);
            if ($this->isComponentSupportedForCombinedSlate($component)) {
                $combined = $combined->withAdditionalEntry($component);
            }
        }

        return $combined;
    }
}
