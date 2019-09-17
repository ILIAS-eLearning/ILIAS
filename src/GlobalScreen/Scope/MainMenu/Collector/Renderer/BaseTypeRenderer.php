<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Class BaseTypeRenderer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BaseTypeRenderer implements TypeRenderer
{

    /**
     * @var Factory
     */
    protected $ui_factory;


    /**
     * BaseTypeRenderer constructor.
     */
    public function __construct()
    {
        global $DIC;
        $this->ui_factory = $DIC->ui()->factory();
    }


    /**
     * @inheritDoc
     */
    public function getComponentForItem(isItem $item) : Component
    {
        return $this->ui_factory->legacy("");
    }
}
