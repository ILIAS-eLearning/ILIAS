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

namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\ComponentDecoratorTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformation;
use ILIAS\UI\Component\Legacy\Content;
use Closure;
use ILIAS\GlobalScreen\Scope\VisibilityAvailabilityTrait;
use ILIAS\GlobalScreen\Scope\isDecorateable;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
abstract class AbstractBaseItem implements isItem, isDecorateable
{
    use ComponentDecoratorTrait;
    use VisibilityAvailabilityTrait;

    protected int $position = 0;
    protected ?Closure $active_callable = null;
    protected bool $is_always_available = false;
    protected ?TypeInformation $type_information = null;
    protected ?Content $non_available_reason = null;

    /**
     * AbstractBaseItem constructor.
     * @param IdentificationInterface $provider_identification
     */
    public function __construct(protected IdentificationInterface $provider_identification)
    {
    }

    /**
     * @inheritDoc
     */
    public function getProviderIdentification(): IdentificationInterface
    {
        return $this->provider_identification;
    }

    public function withNonAvailableReason(Content $element): isItem
    {
        $clone = clone $this;
        $clone->non_available_reason = $element;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getNonAvailableReason(): Content
    {
        global $DIC;

        return $this->non_available_reason instanceof Legacy ? $this->non_available_reason : $DIC->ui()->factory()->legacy()->content("");
    }

    /**
     * @inheritDoc
     */
    public function isAlwaysAvailable(): bool
    {
        return $this->is_always_available;
    }

    /**
     * @inheritDoc
     */
    public function withAlwaysAvailable(bool $always_active): isItem
    {
        $clone = clone($this);
        $clone->is_always_available = $always_active;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function withPosition(int $position): isItem
    {
        $clone = clone($this);
        $clone->position = $position;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function setTypeInformation(TypeInformation $information): isItem
    {
        $this->type_information = $information;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTypeInformation(): ?TypeInformation
    {
        return $this->type_information;
    }

    public function isTop(): bool
    {
        if ($this instanceof isInterchangeableItem) {
            $changed = $this->hasChanged();
            if ($this instanceof isChild) {
                return $changed;
            }
            if ($this instanceof isTopItem) {
                return !$changed;
            }
        }

        return $this instanceof isTopItem;
    }
}
