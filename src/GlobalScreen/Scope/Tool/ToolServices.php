<?php namespace ILIAS\GlobalScreen\Scope\Tool;

use ILIAS\GlobalScreen\Scope\Tool\Factory\ToolFactory;
use ILIAS\GlobalScreen\ScreenContext\ContextServices;
use ILIAS\GlobalScreen\SingletonTrait;

/**
 * Class ToolServices
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ToolServices
{

    use SingletonTrait;


    /**
     * @return ToolFactory
     */
    public function factory() : ToolFactory
    {
        return $this->get(ToolFactory::class);
    }


    /**
     * @return ContextServices
     */
    public function context() : ContextServices
    {
        return $this->get(ContextServices::class);
    }
}
