<?php

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

declare(strict_types=1);

namespace ILIAS\LearningSequence\Player;

use ILIAS\UI\Factory;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Card\Card;
use ILIAS\UI\Component\Image\Image;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\ILIASObject\Properties\CoreProperties\TileImage\TileImage;
use ilLanguage;

/**
 * Reusable "template" for the adaptive branch-selection page.
 *
 * When the learner reaches a branch (more than one allowed successor), this
 * builder renders a deck with one repository object card per selectable
 * object, like the tiles in the repository: the tile image with the object
 * icon, the title, the description and a button that starts the object.
 *
 * The rendering is deliberately encapsulated here (and not in the player) so it
 * can be reused later, e.g. by the map view or wherever an object choice needs
 * to be presented.
 */
class LSChoicePageBuilder
{
    public const HEADLINE = 'lso_choice_page_headline';
    public const START_LABEL = 'lso_choice_page_start_label';

    public function __construct(
        protected Factory $ui_factory,
        protected ilLanguage $lang,
        protected \ilObjectDataCache $obj_data_cache,
        protected \LSUrlBuilder $url_builder,
        protected string $goto_command
    ) {
    }

    /**
     * The friendly statement shown above the cards; usable as page title.
     */
    public function getHeadline(): string
    {
        return $this->lang->txt(self::HEADLINE);
    }

    /**
     * Builds the choice page content: a deck with one card per selectable
     * successor.
     *
     * @param \LSLearnerItem[] $successors
     * @return Component[]
     */
    public function build(array $successors): array
    {
        $this->preloadObjects($successors);

        return [
            $this->ui_factory->deck(array_map(
                fn(\LSLearnerItem $successor): Card => $this->buildCard($successor),
                $successors
            ))->withSmallCardsSize()
        ];
    }

    /**
     * Builds the card for one selectable object.
     */
    public function buildCard(\LSLearnerItem $item): Card
    {
        $start_url = $this->getStartUrl($item);

        return $this->ui_factory->card()->repositoryObject(
            htmlspecialchars($item->getTitle()),
            $this->getTileImage($item)->withAction($start_url)
        )->withObjectIcon(
            $this->getObjectIcon($item)
        )->withTitleAction(
            $start_url
        )->withSections(
            $this->getSections($item, $start_url)
        );
    }

    protected function getStartUrl(\LSLearnerItem $item): string
    {
        return $this->url_builder->getHref(
            $this->goto_command,
            $this->obj_data_cache->lookupObjId($item->getRefId())
        );
    }

    /**
     * @return Component[]
     */
    protected function getSections(\LSLearnerItem $item, string $start_url): array
    {
        $sections = [];
        $description = trim($item->getDescription());
        if ($description !== '') {
            $sections[] = $this->ui_factory->listing()->property()
                ->withProperty($this->lang->txt('description'), $description, false);
        }
        $sections[] = $this->ui_factory->button()->standard($this->lang->txt(self::START_LABEL), $start_url);

        return $sections;
    }

    protected function getObjectIcon(\LSLearnerItem $item): Icon
    {
        $label = $this->lang->txt('obj_' . $item->getType());
        if ($item->getIconPath() !== '') {
            return $this->ui_factory->symbol()->icon()->custom($item->getIconPath(), $label, Icon::MEDIUM);
        }

        return $this->ui_factory->symbol()->icon()->standard($item->getType(), $label, Icon::MEDIUM);
    }

    /**
     * @param \LSLearnerItem[] $successors
     */
    protected function preloadObjects(array $successors): void
    {
        $this->obj_data_cache->preloadReferenceCache(
            array_map(static fn(\LSLearnerItem $successor): int => $successor->getRefId(), $successors)
        );
    }

    /**
     * The object's tile image, or the default tile of the object type, as in
     * the tiles of the repository.
     */
    protected function getTileImage(\LSLearnerItem $item): Image
    {
        $tile_image = $this->getObjectTileImage($item);
        if ($tile_image !== null) {
            return $tile_image->getImage($this->ui_factory->image())->withAlt($item->getTitle());
        }

        $path = \ilUtil::getImagePath('cont_tile/cont_tile_default_' . $item->getType() . '.svg');
        if (!is_file($path)) {
            $path = \ilUtil::getImagePath('cont_tile/cont_tile_default.svg');
        }

        return $this->ui_factory->image()->responsive($path, $item->getTitle());
    }

    protected function getObjectTileImage(\LSLearnerItem $item): ?TileImage
    {
        $obj_id = $this->obj_data_cache->lookupObjId($item->getRefId());
        $object = $obj_id > 0 ? \ilObjectFactory::getInstanceByObjId($obj_id, false) : null;

        return $object?->getObjectProperties()->getPropertyTileImage()->getTileImage();
    }
}
