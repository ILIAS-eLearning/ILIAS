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
use ilLink;
use ilObject;

class BadgeParent
{
    /** @var Closure(int, string, string): string */
    private readonly Closure $icon;
    /** @var Closure(int): int[] */
    private readonly Closure $references_of;
    /** @var Closure(int): string */
    private readonly Closure $link_to;

    /**
     * @param Closure(int, string, string): string $icon
     * @param Closure(int): int[] $references_of
     * @param Closure(int): string $link_to
     */
    public function __construct(
        private readonly Container $container,
        $icon = [ilObject::class, '_getIcon'],
        $references_of = [ilObject::class, '_getAllReferences'],
        $link_to = [ilLink::class, '_getLink']
    ) {
        $this->icon = Closure::fromCallable($icon);
        $this->references_of = Closure::fromCallable($references_of);
        $this->link_to = Closure::fromCallable($link_to);
    }

    public function asComponent(ilBadge $badge): ?Component
    {
        $meta_data = $this->metaData($badge);
        if (null === $meta_data) {
            return null;
        }

        $parent_icon = $this->container->ui()->factory()->symbol()->icon()->custom(
            ($this->icon)($meta_data['id'], 'big', $meta_data['type']),
            $this->container->language()->txt('obj_' . $meta_data['type']),
            'medium'
        );

        $parent_ref_id = current(($this->references_of)($meta_data['id']));
        if ($parent_ref_id && $this->container->access()->checkAccess('read', '', $parent_ref_id)) {
            $parent_link = $this->container->ui()
                                           ->factory()
                                           ->link()
                                           ->standard($meta_data['title'], ($this->link_to)($parent_ref_id));
        } else {
            $parent_link = $this->container->ui()->factory()->legacy($meta_data['title']);
        }

        return $this->container->ui()->factory()->listing()->descriptive([
            $this->container->language()->txt('object') => $this->container->ui()->factory()->legacy(
                $this->container->ui()->renderer()->render([$parent_icon, $parent_link])
            )
        ]);
    }

    public function asProperty(ilBadge $badge): ?string
    {
        $meta_data = $this->metaData($badge);
        if (null === $meta_data) {
            return null;
        }

        $icon = [$this->container->ui()->factory()->symbol()->icon(), 'custom'];

        return implode(' ', [
            $this->container->ui()->renderer()->render($icon(($this->icon)($meta_data['id'], 'small', $meta_data['type']), $meta_data['title'])),
            $meta_data['title'],
        ]);
    }

    private function metaData(ilBadge $badge): ?array
    {
        if (!$badge->getParentId()) {
            return null;
        }
        $parent = $badge->getParentMeta();
        if ($parent['type'] === 'bdga') {
            return null;
        }

        return $parent;
    }
}
