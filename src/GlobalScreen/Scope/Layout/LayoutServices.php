<?php namespace ILIAS\GlobalScreen\Scope\Layout;

use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;

/**
 * Class LayoutServices
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LayoutServices
{

    /**
     * @var ModifierServices
     */
    private $modifiers;
    /**
     * @var MetaContent
     */
    private $meta_content;


    /**
     * LayoutServices constructor.
     */
    public function __construct()
    {
        $this->meta_content = new MetaContent();
        $this->modifiers = new ModifierServices();
    }


    public function modifiers() : ModifierServices
    {
        return $this->modifiers;
    }


    /**
     * @return MetaContent
     */
    public function meta() : MetaContent
    {
        return $this->meta_content;
    }
}
