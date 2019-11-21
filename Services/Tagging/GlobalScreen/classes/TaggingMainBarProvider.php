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

        $title = $this->dic->language()->txt("mm_tags");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("simpleline/tag.svg"), $title);

        return [
            $this->mainmenu->complex($this->if->identifier('tags'))
                ->withTitle($title)
                ->withSymbol($icon)
                ->withContentWrapper(function () {
                    $tag_ui = new \ilTaggingSlateContentGUI();

                    return $this->dic->ui()->factory()->legacy($tag_ui->render());
                })
                ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
                ->withPosition(20),
        ];
    }
}
