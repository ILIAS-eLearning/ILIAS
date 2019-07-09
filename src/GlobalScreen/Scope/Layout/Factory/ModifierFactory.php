<?php namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

/**
 * Class ModifierFactory
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ModifierFactory
{

    /**
     * @return Content
     */
    public function content() : Content
    {
        return new Content();
    }


    /**
     * @return Logo
     */
    public function logo() : Logo
    {
        return new Logo;
    }
}
