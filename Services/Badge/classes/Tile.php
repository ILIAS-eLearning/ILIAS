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

namespace ILIAS\Badge;

use Closure;
use ILIAS\DI\Container;
use ILIAS\UI\Component\Card\Card;
use ILIAS\UI\Component\Component;
use ilBadge;
use ilBadgeAssignment;
use ilDatePresentation;
use ilDateTime;
use ilDateTimeException;
use ilWACSignedPath;

class Tile
{
    /** @var Closure(string): string */
    private readonly Closure $sign_file;
    private readonly BadgeParent $parent;
    private readonly Modal $modal;
    /** @var Closure(int): string */
    private readonly Closure $format_date;

    /**
     * @param Closure(string): string $sign_file
     * @param Closure(int): string $format_date
     */
    public function __construct(
        private readonly Container $container,
        BadgeParent $parent = null,
        Modal $modal = null,
        $sign_file = [ilWACSignedPath::class, 'signFile'],
        Closure $format_date = null,
    ) {
        $this->parent = $parent ?? new BadgeParent($this->container);
        $this->modal = $modal ?? new Modal($this->container);
        $this->sign_file = Closure::fromCallable($sign_file);
        if (!$format_date) {
            class_exists(ilDateTime::class); // Ensure ilDateTime is loaded as IL_CAL_UNIX is defined in ilDateTime.php.
            $format_date = static fn($date, int $format = IL_CAL_UNIX): string => (
                ilDatePresentation::formatDate(new ilDateTime($date, $format))
            );
        }
        $this->format_date = $format_date;
    }

    /**
     * @return array{modal: Component, card: Component}
     */
    public function inDeck(ilBadge $badge, ilBadgeAssignment $assignment, string $gui): array
    {
        $parent = $this->parent->asComponent($badge);
        $badge_sections = $parent ? [$parent] : [];
        $badge_sections[] = $this->profileButton($badge, $assignment, $gui);

        $content = $this->modalContentWithAssignment($badge, $assignment);
        $card = $this->card($content);
        $modal = $this->modal($card);
        $image = $this->image($modal, $badge);
        $card = $card->withSections($badge_sections)
                     ->withImage($image)
                     ->withTitleAction($modal->getShowSignal());

        return [
            'card' => $card,
            'modal' => $modal,
        ];
    }

    /**
     * @return list<Component>
     */
    public function asImage(ModalContent $content): array
    {
        $modal = $this->modal($this->card($content));
        return [
            $modal,
            $this->image($modal, $content->badge()),
        ];
    }

    /**
     * @return list<Component>
     */
    public function asTitle(ModalContent $content): array
    {
        $modal = $this->modal($this->card($content));
        return [
            $modal,
            $this->image($modal, $content->badge()),
            $this->title($modal, $content->badge()),
        ];
    }

    private function card(ModalContent $content)
    {
        return $this->container
            ->ui()
            ->factory()
            ->card()
            ->standard($content->badge()->getTitle())
            ->withHiddenSections($this->modal->components($content));
    }

    private function modal(Card $card): Component
    {
        return $this->container->ui()->factory()->modal()->lightbox(
            $this->container->ui()->factory()->modal()->lightboxCardPage($card)
        );
    }

    private function image(Component $modal, ilBadge $badge): Component
    {
        return $this->container->ui()
                               ->factory()
                               ->image()
                               ->responsive(($this->sign_file)($badge->getImagePath()), $badge->getImage())
                               ->withAction($modal->getShowSignal());
    }

    private function title(Component $modal, ilBadge $badge): Component
    {
        return $this->container->ui()
                               ->factory()
                               ->button()
                               ->shy($badge->getTitle(), $modal->getShowSignal());
    }

    public function modalContent(ilBadge $badge): ModalContent
    {
        $awarded_by = $this->parent->asProperty($badge);
        return new ModalContent($badge, [
            $this->txt('criteria') => $badge->getCriteria(),
            ...(null !== $awarded_by ? [$this->txt('awarded_by') => $awarded_by] : []),
            $this->txt('valid_until') => $this->tryFormating($badge->getValid()),
        ]);
    }

    public function modalContentWithAssignment(ilBadge $badge, ilBadgeAssignment $assignment): ModalContent
    {
        return $this->addAssignment($this->modalContent($badge), $assignment);
    }

    public function addAssignment(ModalContent $content, ilBadgeAssignment $assignment): ModalContent
    {
        return $content->withAdditionalProperties([
            $this->txt('issued_on') => ($this->format_date)($assignment->getTimestamp()),
        ]);
    }

    private function txt(string $key): string
    {
        return $this->container->language()->txt($key);
    }

    private function tryFormating(string $valid): string
    {
        if (!$valid) {
            return $this->txt('endless');
        }
        try {
            return ($this->format_date)($valid, IL_CAL_DATE);
        } catch (ilDateTimeException $x) {
            return $valid;
        }
    }

    private function profileButton(ilBadge $badge, ilBadgeAssignment $assignment, string $gui): Component
    {
        $active = $assignment->getPosition();

        $this->container->ctrl()->setParameterByClass($gui, 'badge_id', $badge->getId());

        $url = $this->container->ctrl()->getLinkTargetByClass(
            $gui,
            $active ? 'deactivateInCard' : 'activateInCard'
        );

        $this->container->ctrl()->setParameterByClass($gui, 'badge_id', '');

        return $this->container->ui()->factory()->button()->standard(
            $this->txt($active ? 'badge_remove_from_profile' : 'badge_add_to_profile'),
            $url
        );
    }
}
