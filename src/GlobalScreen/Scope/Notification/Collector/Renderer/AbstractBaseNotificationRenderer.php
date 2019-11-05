<?php namespace ILIAS\GlobalScreen\Scope\Notification\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\Notification\Factory\canHaveSymbol;
use ILIAS\GlobalScreen\Scope\Notification\Factory\isItem;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Factory;

/**
 * Class AbstractBaseNotificationRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseNotificationRenderer implements NotificationRenderer
{

    /**
     * @var Factory
     */
    protected $ui_factory;


    /**
     * StandardNotificationRenderer constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
    }


    /**
     * @param isItem $item
     *
     * @return Symbol
     */
    protected function getStandardSymbol(isItem $item) : Symbol
    {
        if ($item instanceof canHaveSymbol && $item->hasSymbol()) {
            return $item->getSymbol();
        }

        return $this->ui_factory->symbol()->icon()->custom("./src/UI/examples/Layout/Page/Standard/question.svg", 'ILIAS', 'small', true);
    }
}
