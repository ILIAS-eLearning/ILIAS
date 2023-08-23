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

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use LogicException;
use Generator;
use InvalidArgumentException;
use ILIAS\UI\Implementation\Component\Input\DynamicInputsNameSource;

/**
 * This implements commonalities between inputs.
 */
abstract class Input implements InputInternal
{
    use ComponentHelper;

    /**
     * This is the value contained in the input as displayed
     * client side.
     *
     * @var    mixed
     */
    protected $value = null;

    /**
     * This is an error on the input as displayed client side.
     */
    protected ?string $error = null;

    private ?string $name = null;

    protected ?string $dedicated_name = null;

    /**
     * This is the current content of the input in the abstraction. This results by
     * applying the transformations and constraints to the value(s) (@see: operations)
     * Note that the content is only calculated by applying the withInput function.
     */
    protected ?Result $content = null;

    /**
     * @var Transformation[]
     */
    protected array $operations = [];

    protected DataFactory $data_factory;
    protected Refinery $refinery;

    /**
     * Input constructor.
     */
    public function __construct(
        DataFactory $data_factory,
        Refinery $refinery
    ) {
        $this->data_factory = $data_factory;
        $this->refinery = $refinery;
    }

    /**
     * Get the value that is displayed in the input client side.
     *
     * @return    mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get an input like this with another value displayed on the
     * client side.
     *
     * @param   mixed $value
     * @throws  InvalidArgumentException    if value does not fit client side input
     */
    public function withValue($value): self
    {
        $this->checkArg("value", $this->isClientSideValueOk($value), "Display value does not match input type.");
        $clone = clone $this;
        $clone->value = $value;
        return $clone;
    }

    /**
     * Check if the value is good to be displayed client side.
     *
     * @param mixed $value
     */
    abstract public function isClientSideValueOk($value): bool;

    /**
     * The error of the input as used in HTML.
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Get an input like this one, with a different error.
     */
    public function withError(string $error): self
    {
        $clone = clone $this;
        $clone->setError($error);
        return $clone;
    }

    /**
     * Set an error on this input.
     */
    private function setError(string $error): void
    {
        $this->error = $error;
    }

    /**
     * Apply a transformation to the current or future content.
     */
    public function withAdditionalTransformation(Transformation $trafo): self
    {
        $clone = clone $this;
        $clone->setAdditionalTransformation($trafo);
        return $clone;
    }

    /**
     * Apply a transformation to the current or future content.
     *
     * ATTENTION: This is a real setter, i.e. it modifies $this! Use this only if
     * `withAdditionalTransformation` does not work, i.e. in the constructor.
     */
    protected function setAdditionalTransformation(Transformation $trafo): void
    {
        $this->operations[] = $trafo;
        if ($this->content !== null) {
            if (!$this->content->isError()) {
                $this->content = $trafo->applyTo($this->content);
            }
            if ($this->content->isError()) {
                $this->setError($this->content->error());
            }
        }
    }

    /**
     * @inheritdoc
     */
    final public function getDedicatedName(): ?string
    {
        return $this->dedicated_name;
    }

    /**
     * @inheritdoc
     */
    final public function withDedicatedName(string $dedicated_name): self
    {
        $clone = clone $this;
        $clone->dedicated_name = $dedicated_name;
        return $clone;
    }

    /**
     * @inheritdoc
     */
    final public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function withNameFrom(NameSource $source, ?string $parent_name = null): self
    {
        $clone = clone $this;
        if ($source instanceof DynamicInputsNameSource) {
            $clone->name = '';
        } else {
            $clone->name = ($parent_name !== null) ? $parent_name . '/' : '';
        }
        $clone->name .= ($clone->dedicated_name !== null)
                        ? $source->getNewDedicatedName($clone->dedicated_name)
                        : $source->getNewName();
        return $clone;
    }

    /**
     * Collects the input, applies trafos on the input and returns
     * a new input reflecting the data that was put in.
     *
     * @inheritdoc
     */
    public function withInput(InputData $input): self
    {
        if ($this->getName() === null) {
            throw new LogicException("Can only collect if input has a name.");
        }


        //TODO: Discuss, is this correct here. If there is no input contained in this post
        //We assign null. Note that unset checkboxes are not contained in POST.
        $value = $input->getOr($this->getName(), null);
        // ATTENTION: There was a special case for the Filter Input Container here,
        // which lead to #27909. The issue will most certainly appear again in. If
        // you are the one debugging it and came here: Please don't put knowledge
        // of the special case for the filter in this general class. Have a look
        // into https://mantis.ilias.de/view.php?id=27909 for the according discussion.
        $clone = $this->withValue($value);

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

    /**
     * Applies the operations in this instance to the value.
     *
     * @param    mixed $res
     */
    protected function applyOperationsTo($res): Result
    {
        $res = $this->data_factory->ok($res);
        foreach ($this->getOperations() as $op) {
            if ($res->isError()) {
                return $res;
            }
            $res = $op->applyTo($res);
        }

        return $res;
    }

    /**
     * Get the operations that should be performed on the input.
     *
     * @return Generator<Transformation>
     */
    protected function getOperations(): Generator
    {
        foreach ($this->operations as $op) {
            yield $op;
        }
    }

    /**
     * @inheritdoc
     */
    public function getContent(): Result
    {
        if (is_null($this->content)) {
            throw new LogicException("No content of this field has been evaluated yet. Seems withRequest was not called.");
        }
        return $this->content;
    }
}
