<?php declare(strict_types=1);

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\Triggerable;
use ILIAS\Refinery\Constraint;
use Closure;

/**
 * This implements the password input.
 */
class Password extends Input implements C\Input\Field\Password, Triggerable
{
    use ComponentHelper;
    use JavaScriptBindable;

    private ?bool $revelation = null;
    protected Signal $signal_reveal;
    protected Signal $signal_mask;
    protected SignalGeneratorInterface $signal_generator;

    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        string $label,
        ?string $byline,
        SignalGeneratorInterface $signal_generator
    ) {
        parent::__construct($data_factory, $refinery, $label, $byline);

        $this->signal_generator = $signal_generator;
        $trafo = $this->refinery->to()->data('password');
        $this->setAdditionalTransformation($trafo);
        $this->initSignals();
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value) : bool
    {
        return is_string($value);
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement() : ?Constraint
    {
        return $this->refinery->string()->hasMinLength(1);
    }

    /**
     * This is a shortcut to quickly get a password-field with desired constraints.
     */
    public function withStandardConstraints(
        int $min_length = 8,
        bool $lower = true,
        bool $upper = true,
        bool $numbers = true,
        bool $special = true
    ) : C\Input\Field\Input {
        $pw_validation = $this->refinery->password();
        $constraints = [
            $this->refinery->string()->hasMinLength($min_length),
        ];

        if ($lower) {
            $constraints[] = $pw_validation->hasLowerChars();
        }
        if ($upper) {
            $constraints[] = $pw_validation->hasUpperChars();
        }
        if ($numbers) {
            $constraints[] = $pw_validation->hasNumbers();
        }
        if ($special) {
            $constraints[] = $pw_validation->hasSpecialChars();
        }

        return $this->withAdditionalTransformation($this->refinery->logical()->parallel($constraints));
    }

    /**
     * Get a Password like this with the revelation-option enabled (or disabled).
     */
    public function withRevelation(bool $revelation) : Password
    {
        $clone = clone $this;
        $clone->revelation = $revelation;
        return $clone;
    }

    /**
     * Get the status of the revelation-option.
     */
    public function getRevelation() : ?bool
    {
        return $this->revelation;
    }

    /**
     * Set the signals for this component.
     */
    protected function initSignals() : void
    {
        $this->signal_reveal = $this->signal_generator->create();
        $this->signal_mask = $this->signal_generator->create();
    }

    /**
     * Reset all Signals.
     */
    public function withResetSignals() : Triggerable
    {
        $clone = clone $this;
        $clone->initSignals();
        return $clone;
    }

    /**
     * Get the signal for unmasking the input.
     */
    public function getRevealSignal() : Signal
    {
        return $this->signal_reveal;
    }

    /**
     * Get the signal for masking the input.
     */
    public function getMaskSignal() : Signal
    {
        return $this->signal_mask;
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode() : Closure
    {
        return function ($id) {
            return "$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id').find('input').val().replace(/./g, '*'));
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id').find('input').val().replace(/./g, '*'));";
        };
    }
}
