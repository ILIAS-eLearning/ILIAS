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
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Scope\ComponentDecoratorTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformation;
use ILIAS\UI\Component\Legacy\Legacy;
use Closure;

/**
 * Class AbstractBaseItem
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseItem implements isItem
{
    use ComponentDecoratorTrait;

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var bool|null
     */
    private $is_visible_static;

    /**
     * @var \ILIAS\GlobalScreen\Identification\IdentificationInterface
     */
    protected $provider_identification;
    /**
     * @var \Closure|null
     */
    protected $available_callable;
    /**
     * @var \Closure|null
     */
    protected $active_callable;
    /**
     * @var \Closure|null
     */
    protected $visiblility_callable;
    /**
     * @var bool
     */
    protected $is_always_available = false;
    /**
     * @var \ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformation|null
     */
    protected $type_information;
    /**
     * @var \ILIAS\UI\Component\Legacy\Legacy|null
     */
    protected $non_available_reason;

    /**
     * AbstractBaseItem constructor.
     * @param IdentificationInterface $provider_identification
     */
    public function __construct(IdentificationInterface $provider_identification)
    {
        $this->provider_identification = $provider_identification;
    }

    /**
     * @inheritDoc
     */
    public function getProviderIdentification() : IdentificationInterface
    {
        return $this->provider_identification;
    }

    /**
     * @inheritDoc
     */
    public function withVisibilityCallable(callable $is_visible) : isItem
    {
        $clone = clone($this);
        $clone->visiblility_callable = $is_visible;
        $clone->is_visible_static = null;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function isVisible() : bool
    {
        if (isset($this->is_visible_static)) {
            return $this->is_visible_static;
        }
        if (!$this->isAvailable()) {
            return $this->is_visible_static = false;
        }
        if (is_callable($this->visiblility_callable)) {
            $callable = $this->visiblility_callable;

            $value = (bool) $callable();

            return $this->is_visible_static = $value;
        }

        return $this->is_visible_static = true;
    }

    /**
     * @inheritDoc
     */
    public function withAvailableCallable(callable $is_available) : isItem
    {
        $clone = clone($this);
        $clone->available_callable = $is_available;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function isAvailable() : bool
    {
        if ($this->isAlwaysAvailable() === true) {
            return true;
        }
        if (is_callable($this->available_callable)) {
            $callable = $this->available_callable;

            return (bool) $callable();
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function withNonAvailableReason(Legacy $element) : isItem
    {
        $clone = clone $this;
        $clone->non_available_reason = $element;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getNonAvailableReason() : Legacy
    {
        global $DIC;

        return $this->non_available_reason instanceof Legacy ? $this->non_available_reason : $DIC->ui()->factory()->legacy("");
    }

    /**
     * @inheritDoc
     */
    public function isAlwaysAvailable() : bool
    {
        return $this->is_always_available;
    }

    /**
     * @inheritDoc
     */
    public function withAlwaysAvailable(bool $always_active) : isItem
    {
        $clone = clone($this);
        $clone->is_always_available = $always_active;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getPosition() : int
    {
        return $this->position;
    }

    /**
     * @inheritDoc
     */
    public function withPosition(int $position) : isItem
    {
        $clone = clone($this);
        $clone->position = $position;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function setTypeInformation(TypeInformation $information) : isItem
    {
        $this->type_information = $information;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTypeInformation() : ?TypeInformation
    {
        return $this->type_information;
    }

    public function isTop() : bool
    {
        if ($this instanceof isInterchangeableItem) {
            $changed = $this->hasChanged();
            if ($this instanceof isChild) {
                return $changed;
            } elseif ($this instanceof isTopItem) {
                return !$changed;
            }
        }

        return $this instanceof isTopItem;
    }
}
