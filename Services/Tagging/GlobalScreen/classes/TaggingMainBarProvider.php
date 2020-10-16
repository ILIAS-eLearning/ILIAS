<?php namespace ILIAS\Tagging\Provider;

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;
use ilTaggingSlateContentMenuGUI;

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
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::TAGS, $title)->withIsOutlined(true);

        return [
            $this->mainmenu->complex($this->if->identifier('tags'))
                ->withAvailableCallable(function () {
                    $tags_set = new \ilSetting("tags");
                    return (bool) $tags_set->get("enable");
                })
                ->withTitle($title)
                ->withSupportsAsynchronousLoading(true)
                ->withSymbol($icon)
                ->withContentWrapper(function () {
                    $tag_ui = new ilTaggingSlateContentMenuGUI();

                    return $this->dic->ui()->factory()->legacy($tag_ui->render());
                })
                ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
                ->withPosition(20),
        ];
    }
}
