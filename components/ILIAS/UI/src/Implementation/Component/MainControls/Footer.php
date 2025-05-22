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

namespace ILIAS\UI\Implementation\Component\MainControls;

use ILIAS\UI\Component\MainControls;
use ILIAS\UI\Component\Modal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Link\Standard as Link;
use ILIAS\UI\Component\Symbol\Icon\Icon;
use ILIAS\UI\Component\Button\Shy;
use ILIAS\UI\Component\Signal;
use ILIAS\Data\URI;

class Footer implements MainControls\Footer
{
    use ComponentHelper;

    /** @var Modal\RoundTrip[] */
    private array $modals = [];

    protected ?URI $permanent_url = null;

    /** @var array<array{0: string, 1: array<Link|Shy}> (use as [$title, $actions] = <entry>) */
    protected array $link_groups = [];

    /** @var array<array{0: Icon, 1: Signal|URI|null}> (use as [$icon, $action] = <entry>) */
    protected array $icons = [];

    /** @var array<Link|Shy> */
    protected array $links = [];

    /** @var string[] */
    protected array $texts = [];

    public function withAdditionalLinkGroup(string $title, array $actions): MainControls\Footer
    {
        $this->checkArgListElements('actions', $actions, [Link::class, Shy::class]);

        $clone = clone $this;
        $clone->link_groups[] = [$title, $actions];
        return $clone;
    }

    /**
     * @return array<array{0: string, 1: array<Link|Shy}> (use as [$title, $actions] = <entry>)
     */
    public function getAdditionalLinkGroups(): array
    {
        return $this->link_groups;
    }

    public function withAdditionalLink(Link|Shy ...$actions): MainControls\Footer
    {
        $this->checkArgListElements('actions', $actions, [Link::class, Shy::class]);

        $clone = clone $this;
        array_push($clone->links, ...$actions);
        return $clone;
    }

    /**
     * @return array<Link|Shy>
     */
    public function getAdditionalLinks(): array
    {
        return $this->links;
    }

    public function withAdditionalIcon(Icon $icon, Signal|URI|null $action = null): MainControls\Footer
    {
        $clone = clone $this;
        $clone->icons[] = [$icon, $action];
        return $clone;
    }

    /**
     * @return array<array{0: Icon, 1: Signal|URI|null}> (use as [$icon, $action] = <entry>)
     */
    public function getAdditionalIcons(): array
    {
        return $this->icons;
    }

    public function withAdditionalText(string ...$texts): MainControls\Footer
    {
        $clone = clone $this;
        array_push($clone->texts, ...$texts);
        return $clone;
    }

    /**
     * @return string[]
     */
    public function getAdditionalTexts(): array
    {
        return $this->texts;
    }

    public function withPermanentURL(URI $url): MainControls\Footer
    {
        $clone = clone $this;
        $clone->permanent_url = $url;
        return $clone;
    }

    public function getPermanentURL(): ?URI
    {
        return $this->permanent_url;
    }

    /**
     * @return Modal\RoundTrip[]
     */
    public function getModals(): array
    {
        return $this->modals;
    }

    public function withAdditionalModal(Modal\RoundTrip $modal): MainControls\Footer
    {
        $clone = clone $this;
        $clone->modals[] = $modal;
        return $clone;
    }

}
