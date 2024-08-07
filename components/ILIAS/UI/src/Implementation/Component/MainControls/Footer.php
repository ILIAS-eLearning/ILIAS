<?php

declare(strict_types=1);

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

    /**
     * @var [Modal\RoundTrip, Button\Shy][]
     */
    private array $modalsWithTriggers = [];
    protected ?URI $permanent_url = null;

    /**
     * @inheritDoc
     */
    public function withAdditionalLinkGroup(string $title, array $actions): MainControls\Footer
    {
        throw new \ILIAS\UI\NotImplementedException();
    }

    public function withAdditionalLink(Link|Shy ...$actions): MainControls\Footer
    {
        throw new \ILIAS\UI\NotImplementedException();
    }

    public function withAdditionalIcon(Icon $icon, Signal|URI|null $action = null): MainControls\Footer
    {
        throw new \ILIAS\UI\NotImplementedException();
    }

    public function withAdditionalText(string ...$texts): MainControls\Footer
    {
        throw new \ILIAS\UI\NotImplementedException();
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
     * @return array containing entries with [Modal\RoundTrip, Button\Shy]
     */
    public function getModals(): array
    {
        return $this->modalsWithTriggers;
    }

    public function withAdditionalModalAndTrigger(
        Modal\RoundTrip $roundTripModal,
        Shy $shyButton
    ): self {
        $shyButton = $shyButton->withOnClick($roundTripModal->getShowSignal());

        $clone = clone $this;
        $clone->modalsWithTriggers[] = [$roundTripModal, $shyButton];
        return $clone;
    }
}
