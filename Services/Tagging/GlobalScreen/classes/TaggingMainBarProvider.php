<?php

declare(strict_types=1);

namespace ILIAS\Tagging\Provider;

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\GlobalScreen\Scope\MainMenu\Provider\AbstractStaticMainMenuProvider;
use ILIAS\MainMenu\Provider\StandardTopItemsProvider;
use ILIAS\UI\Component\Symbol\Icon\Standard;

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
    public function getStaticTopItems(): array
    {
        return [];
    }


    /**
     * @inheritDoc
     */
    public function getStaticSubItems(): array
    {
        $title = $this->dic->language()->txt("mm_tags");
        $icon = $this->dic->ui()->factory()->symbol()->icon()->standard(Standard::TAGS, $title);

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
                    $tag_ui = new \ilTaggingSlateContentGUI();

                    return $this->dic->ui()->factory()->legacy($tag_ui->render());
                })
                ->withParent(StandardTopItemsProvider::getInstance()->getPersonalWorkspaceIdentification())
                ->withPosition(20),
        ];
    }
}
