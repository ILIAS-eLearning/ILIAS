<?php namespace ILIAS\Tagging\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;

/**
 * Class TaggingMainBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TaggingMainBarProvider extends AbstractStaticMainMenuProvider
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
        $f = $this->dic->ui()->factory();

        $icon = $f->symbol()->glyph()->comment();
        $contents = $f->legacy("some contents.");

        return [
            $this->mainmenu->complex($this->if->identifier('tags2'))
                ->withSymbol($icon)
                ->withContent($contents)
                ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
                ->withAlwaysAvailable(true)
                ->withPosition(19)
            ,
            $this->mainmenu->link($this->if->identifier('tags'))
                ->withTitle($this->dic->language()->txt("mm_tags"))
                ->withAction("#")
                ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
                ->withSymbol($this->dic->ui()->factory()->symbol()->icon()->standard("tags", "")->withIsOutlined(true))
                ->withPosition(20),
        ];
    }
}
