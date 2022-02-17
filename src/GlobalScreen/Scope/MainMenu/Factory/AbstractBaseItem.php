<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Scope\ComponentDecoratorTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformation;
use ILIAS\UI\Component\Legacy\Legacy;

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
     * @var Legacy
     */
    protected $non_available_reason;
    /**
     * @var
     */
    protected $available_callable = true;
    /**
     * @var callable
     */
    protected $active_callable;
    /**
     * @var IdentificationInterface
     */
    protected $provider_identification;
    /**
     * @var callable
     */
    protected $visiblility_callable;
    /**
     * @var bool
     */
    protected $is_always_available = false;
    /**
     * @var
     */
    protected $type_information;
    /**
     * @var bool
     */
    private $is_visible_static;

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

            $value = $callable();

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

            return $callable();
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
    public function getTypeInformation() : TypeInformation
    {
        return $this->type_information instanceof TypeInformation ? $this->type_information : new TypeInformation(get_class($this), get_class($this));
    }

    public function isTop() : bool
    {
        if ($this instanceof isChild) {
            return $this->getParent() instanceof NullIdentification || (int) $this->getParent()->serialize() === false;
        }
        if ($this instanceof isTopItem && $this instanceof isInterchangeableItem) {
            return $this->getParent() === null || $this->getParent() instanceof NullIdentification;
        }
        return $this instanceof isTopItem;
    }
}
