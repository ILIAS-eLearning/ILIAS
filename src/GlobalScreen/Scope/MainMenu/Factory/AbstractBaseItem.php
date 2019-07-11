<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Information\TypeInformation;
use ILIAS\UI\Component\Legacy\Legacy;

/**
 * Class AbstractBaseItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class AbstractBaseItem implements isItem
{

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
     * AbstractBaseItem constructor.
     *
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
        if (!$this->isAvailable()) {
            return false;
        }
        if (is_callable($this->visiblility_callable)) {
            $callable = $this->visiblility_callable;

            $value = $callable();

            return $value;
        }

        return true;
    }


    /**
     * @inheritDoc
     */
    public function withActiveCallable(callable $is_active) : isItem
    {
        $clone = clone($this);
        $clone->active_callable = $is_active;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function isActive() : bool
    {
        if (is_callable($this->active_callable)) {
            $callable = $this->active_callable;

            $value = $callable();

            return $value;
        }

        return true;
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

            $value = $callable();

            return $value;
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
}
