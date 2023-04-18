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

namespace ILIAS\UI\Implementation\Component\Input\ViewControl;

use ILIAS\UI\Component\Input\ViewControl as VCInterface;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\SignalGeneratorInterface;
use ILIAS\UI\Component\Signal;
use ILIAS\Refinery\Transformation;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\Field\InputInternal;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\Data\Result;
use ILIAS\Data\Factory as DataFactory;

abstract class ViewControl implements VCInterface\ViewControl, InputInternal
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;

    protected Signal $change_signal;
    protected $value;
    protected ?Result $content = null;
    protected ?string $name = null;
    protected bool $is_disabled = false;

    /**
     * @var Transformation[]
     */
    protected array $operations = [];

    public function __construct(
        protected DataFactory $data_factory,
        protected Refinery $refinery,
        protected string $label
    ) {
    }

    public function withOnChange(Signal $change_signal): self
    {
        $clone = clone $this;
        $clone->change_signal = $change_signal;
        return $clone;
    }

    public function getOnChangeSignal(): ?Signal
    {
        return $this->change_signal ?? null;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function withLabel(string $label): self
    {
        $clone = clone $this;
        $clone->label = $label;
        return $clone;
    }

    public function isDisabled(): bool
    {
        return $this->is_disabled;
    }

    public function withDisabled(bool $is_disabled): self
    {
        $clone = clone $this;
        $clone->is_disabled = $is_disabled;
        return $clone;
    }

    /**
     * @param mixed $value
     */
    abstract protected function isClientSideValueOk($value): bool;
    abstract protected function getDefaultValue(): string;

    public function withValue($value): self
    {
        $this->checkArg(
            "value",
            $this->isClientSideValueOk($value),
            "Display value does not match input type: " . $this::class . ' - ' . print_r($value, true)
        );
        $clone = clone $this;
        $clone->value = $value;
        return $clone;
    }

    public function withInput(InputData $input)
    {
        if (is_null($this->getName())) {
            throw new \LogicException("Can only collect if control has a name: " . $this::class);
        }

        $input_value = $input->getOr($this->getName(), '');
        if ($input_value === '') {
            $input_value = $this->getDefaultValue();
        }

        $clone = $this->withValue($input_value);

        $clone->content = $this->applyOperationsTo($clone->getValue());
        if ($clone->content->isError()) {
            $error = $clone->content->error();
            if ($error instanceof \Exception) {
                $error = $error->getMessage();
            }
            throw new \InvalidArgumentException(
                'Cannot transform ' . print_r($input_value)
                . $error
            );
        }
        return $clone;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getContent(): Result\Ok
    {
        if (is_null($this->content)) {
            throw new LogicException("No content of this control has been evaluated yet");
        }
        return $this->content;
    }

    final public function getName(): ?string
    {
        return $this->name;
    }

    public function withNameFrom(NameSource $source)
    {
        $clone = clone $this;
        $clone->name = $source->getNewName();
        return $clone;
    }

    final public function withDedicatedName(string $dedicated_name): self
    {
        $clone = clone $this;
        $clone->dedicated_name = $dedicated_name;
        return $clone;
    }

    public function withAdditionalTransformation(Transformation $trafo): self
    {
        $clone = clone $this;
        $clone->operations[] = $trafo;
        return $clone;
    }

    protected function applyOperationsTo($res): Result
    {
        if ($res === null) {
            return $this->data_factory->ok($res);
        }

        $res = $this->data_factory->ok($res);
        foreach ($this->getOperations() as $op) {
            if ($res->isError()) {
                return $res;
            }
            $res = $op->applyTo($res);
        }
        return $res;
    }

    private function getOperations(): \Generator
    {
        foreach ($this->operations as $op) {
            yield $op;
        }
    }
}
