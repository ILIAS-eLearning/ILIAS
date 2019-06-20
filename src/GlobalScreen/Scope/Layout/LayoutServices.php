<?php namespace ILIAS\GlobalScreen\Scope\Layout;

use ILIAS\GlobalScreen\Scope\Layout\MetaContent\MetaContent;
use ILIAS\GlobalScreen\Scope\Layout\Provider\FinalModificationProvider;
use ILIAS\UI\Component\Layout\Page\Page;

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
     * @var FinalModificationProvider[]
     */
    private $final_modification_providers = [];


    /**
     * LayoutServices constructor.
     *
     * @param FinalModificationProvider[] $final_modification_providers
     */
    public function __construct(array $final_modification_providers)
    {
        $this->final_modification_providers = $final_modification_providers;
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


    /**
     * @return Page
     */
    public function final() : Page
    {
        foreach ($this->final_modification_providers as $handler) {
            $handler->modifyGlobalLayout($this->modifiers);
        }

        return $this->modifiers->getPageWithPagePartProviders();
    }
}
