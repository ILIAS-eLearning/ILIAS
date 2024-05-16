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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Refinery\Constraint;
use ILIAS\UI\Implementation\Component\Input\Input;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\Data\Result;
use Generator;

abstract class FormInput extends Input implements FormInputInternal
{
    use JavaScriptBindable;
    use Triggerer;

    protected bool $is_disabled = false;
    protected bool $is_required = false;
    protected ?Constraint $requirement_constraint = null;
    protected string $label;
    protected ?string $byline = null;

    public function __construct(
        DataFactory $data_factory,
        Refinery $refinery,
        string $label,
        ?string $byline = null
    ) {
        parent::__construct($data_factory, $refinery);
        $this->label = $label;
        $this->byline = $byline;
    }

    /**
     * @inheritDoc
     */
    public function withInput(InputData $input)
    {
        if (!$this->isDisabled()) {
            return parent::withInput($input);
        } else {
            $clone = $this;
            $clone->content = $this->applyOperationsTo($clone->getValue());
            if ($clone->content->isError()) {
                $error = $clone->content->error();
                if ($error instanceof \Exception) {
                    $error = $error->getMessage();
                }
                return $clone->withError("" . $error);
            }
            return $clone;
        }
    }

    /**
     * @inheritdoc
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function withLabel(string $label)
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getByline(): ?string
    {
        return $this->byline;
    }

    /**
     * @inheritdoc
     */
    public function withByline(string $byline)
    {
        $clone = clone $this;
        $clone->byline = $byline;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function isRequired(): bool
    {
        return $this->is_required;
    }

    /**
     * @inheritdoc
     */
    public function withRequired(bool $is_required, ?Constraint $requirement_constraint = null)
    {
        $clone = clone $this;
        $clone->is_required = $is_required;
        $clone->requirement_constraint = ($is_required) ? $requirement_constraint : null;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function isDisabled(): bool
    {
        return $this->is_disabled;
    }

    /**
     * @inheritdoc
     */
    public function withDisabled(bool $is_disabled)
    {
        $clone = clone $this;
        $clone->is_disabled = $is_disabled;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withOnUpdate(Signal $signal)
    {
        return $this->withTriggeredSignal($signal, 'update');
    }

    /**
     * @inheritdoc
     */
    public function appendOnUpdate(Signal $signal): self
    {
        return $this->appendTriggeredSignal($signal, 'update');
    }

    /**
     * This may return a constraint that will be checked first if the field is
     * required.
     */
    abstract protected function getConstraintForRequirement(): ?Constraint;

    /**
     * @inheritDoc
     */
    protected function applyOperationsTo($res): Result
    {
        if ($res === null && !$this->isRequired()) {
            return $this->data_factory->ok($res);
        }

        return parent::applyOperationsTo($res);
    }

    /**
     * @inheritDoc
     */
    protected function getOperations(): Generator
    {
        if ($this->isRequired()) {
            $op = $this->getConstraintForRequirement();
            if ($op !== null) {
                yield $op;
            }
        }

        yield from parent::getOperations();
    }
}
