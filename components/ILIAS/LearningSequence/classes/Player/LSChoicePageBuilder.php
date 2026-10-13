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
use ilLanguage;

/**
 * Reusable "template" for the adaptive branch-selection page.
 *
 * When the learner reaches a branch (more than one allowed successor), this
 * builder renders one card ("box") per selectable object using ILIAS UI
 * (Kitchen Sink) components: the object icon, its title, its description and a
 * button that starts the object. The cards are laid out as a responsive deck
 * underneath a short, friendly headline.
 *
 * The rendering is deliberately encapsulated here (and not in the player) so it
 * can be reused later, e.g. by the map view or wherever an object choice needs
 * to be presented.
 */
class LSChoicePageBuilder
{
    public const HEADLINE = 'lso_choice_page_headline';
    public const SUBLINE = 'lso_choice_page_subline';
    public const START_LABEL = 'lso_choice_page_start_label';

    protected ilLanguage $lang;

    public function __construct(
        protected Factory $ui_factory,
        protected \LSUrlBuilder $url_builder,
        protected string $goto_command
    ) {
        global $DIC;

        /** @var \ILIAS\DI\Container $DIC */
        $this->lang = $DIC->language();
    }

    /**
     * The friendly statement shown above the boxes; usable as page title.
     */
    public function getHeadline(): string
    {
        return $this->lang->txt(self::HEADLINE);
    }

    /**
     * Builds the choice page content: a short lead text followed by a deck of
     * one card per selectable successor.
     *
     * @param \LSLearnerItem[] $successors
     * @return Component[]
     */
    public function build(array $successors): array
    {
        $cards = [];
        foreach ($successors as $successor) {
            $cards[] = $this->buildCard($successor);
        }

        $deck = $this->ui_factory->deck($cards)->withNormalCardsSize();

        return [
            $this->ui_factory->legacy()->content(
                '<div class="lso-choice-page"><p class="lead">'
                . htmlspecialchars($this->lang->txt(self::SUBLINE)) . '</p>'
            ),
            $deck,
            $this->ui_factory->legacy()->content('</div>')
        ];
    }

    /**
     * Builds a single "box" (card) for one selectable object.
     */
    public function buildCard(\LSLearnerItem $item): Card
    {
        $sections = [];
        $tile_image = $this->getTileImage($item);

        $sections[] = $this->ui_factory->legacy()->content(
            $this->buildTitleSection($item, $tile_image !== null)
        );
        $description = preg_replace('/\s+/u', ' ', trim($item->getDescription())) ?? '';
        $sections[] = $this->ui_factory->legacy()->content(
            '<p class="lso-choice-card__description"'
            . ($description !== '' ? ' title="' . htmlspecialchars($description, ENT_QUOTES) . '"' : '')
            . '>'
            . ($description !== '' ? htmlspecialchars($description) : '&nbsp;')
            . '</p>'
        );

        $sections[] = $this->ui_factory->button()->standard(
            $this->lang->txt(self::START_LABEL),
            $this->url_builder->getHref(
                $this->goto_command,
                \ilObject::_lookupObjId($item->getRefId())
            )
        );

        $card = $this->ui_factory->card()->standard($item->getTitle());

        if ($tile_image !== null) {
            $card = $card->withImage($tile_image);
        } elseif ($item->getIconPath() !== '') {
            $card = $card->withImage(
                $this->ui_factory->image()->standard($item->getIconPath(), $item->getTitle())
            );
        }

        return $card->withSections($sections);
    }

    protected function buildTitleSection(\LSLearnerItem $item, bool $has_tile_image): string
    {
        $icon = '';
        if ($has_tile_image && $item->getIconPath() !== '') {
            $icon = '<img class="lso-choice-card__title-icon" src="'
                . htmlspecialchars($item->getIconPath(), ENT_QUOTES)
                . '" alt="">';
        }

        $title = htmlspecialchars($item->getTitle());
        $title_attribute = htmlspecialchars($item->getTitle(), ENT_QUOTES);

        return '<div class="lso-choice-card__title-row">'
            . '<span class="lso-choice-card__title" title="' . $title_attribute . '">' . $title . '</span>'
            . $icon
            . '</div>';
    }

    protected function getTileImage(\LSLearnerItem $item): ?Image
    {
        $obj_id = \ilObject::_lookupObjId($item->getRefId());
        if ($obj_id <= 0) {
            return null;
        }

        $object = \ilObjectFactory::getInstanceByObjId($obj_id, false);
        if ($object === null) {
            return null;
        }

        $tile_image = $object->getObjectProperties()
            ->getPropertyTileImage()
            ->getTileImage();
        if ($tile_image === null || $tile_image->getRid() === null || $tile_image->getRid() === '') {
            return null;
        }

        return $tile_image->getImage($this->ui_factory->image())->withAlt($item->getTitle());
    }
}
