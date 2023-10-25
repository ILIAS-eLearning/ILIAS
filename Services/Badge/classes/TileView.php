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
use ILIAS\UI\Component\Component;
use ilBadge;
use ilBadgeAssignment;

class TileView
{
    /** @var Closure(int): ilBadgeAssignment[] */
    private Closure $assignments_of_user;

    /**
     * @param Closure(int): ilBadgeAssignment[] $assignments_of_user
     */
    public function __construct(
        private readonly Container $container,
        private readonly string $gui,
        private readonly Tile $tile,
        private readonly PresentationHeader $head,
        $assignments_of_user = [ilBadgeAssignment::class, 'getInstancesByUserId']
    ) {
        $this->assignments_of_user = Closure::fromCallable($assignments_of_user);
    }

    public function show(): string
    {
        $sort = new Sorting($this->container->http()->request()->getQueryParams()['sort'] ?? '');
        $components = $this->componentsOfBadges($this->sort($sort, $this->badgesAndAssignments()));

        $this->head->show($this->container->language()->txt('tile_view'), $this->sortComponent($sort));

        return $this->container->ui()->renderer()->render($components);
    }

    /**
     * @return list<array{badge: ilBadge, assignment: ilBadgeAssignment}>
     */
    private function badgesAndAssignments(): array
    {
        $badges = [];
        foreach (($this->assignments_of_user)($this->container->user()->getId()) as $assignment) {
            $badge = new ilBadge($assignment->getBadgeId());
            $badges[] = [
                'badge' => $badge,
                'assignment' => $assignment,
            ];
        }

        return $badges;
    }

    /**
     * @param list<array{badge: ilBadge, assignment: ilBadgeAssignment}> $badge_and_assignment
     * @return list<Component>
     */
    private function componentsOfBadges(array $badge_and_assignments): array
    {
        $cards_and_modals = array_map($this->cardsAndModals(...), $badge_and_assignments);
        $components = array_column($cards_and_modals, 'modal');
        $components[] = $this->container->ui()
                                        ->factory()
                                        ->deck(array_column($cards_and_modals, 'card'))
                                        ->withNormalCardsSize();

        return $components;
    }

    /**
     * @param array{badge: ilBadge, assignment: ilBadgeAssignment}
     * @return array{modal: Component, card: Component}
     */
    private function cardsAndModals(array $badge_and_assignments): array
    {
        return $this->tile->inDeck(
            $badge_and_assignments['badge'],
            $badge_and_assignments['assignment'],
            $this->gui
        );
    }

    private function sortComponent(Sorting $sort): Component
    {
        $txt = [$this->container->language(), 'txt'];
        $link = $this->container->ctrl()->getLinkTargetByClass($this->gui, 'listBadges');
        return $this->container->ui()
                               ->factory()
                               ->viewControl()
                               ->sortation(array_map($txt, $sort->options()))
                               ->withTargetURL($link, 'sort')
                               ->withLabel($txt($sort->label()));
    }

    /**
     * @param Sorting $sort
     * @param array{badge: ilBadge, assignment: ilBadgeAssignment}[] $badges_and_assignments
     * @return array{badge: ilBadge, assignment: ilBadgeAssignment}[]
     */
    private function sort(Sorting $sort, array $badges_and_assignments): array
    {
        usort($badges_and_assignments, [$sort, 'compare']);

        return $badges_and_assignments;
    }
}
