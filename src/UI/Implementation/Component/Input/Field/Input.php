<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Result;
use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Factory;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * This implements commonalities between inputs.
 */
abstract class Input implements C\Input\Field\Input, FormInputInternal
{
    use ComponentHelper;
    use JavaScriptBindable;
    use Triggerer;
    /**
     * @var DataFactory
     */
    protected $data_factory;
    /**
     * @var Factory
     */
    protected $refinery;
    /**
     * @var string
     */
    protected $label;
    /**
     * @var string
     */
    protected $byline;
    /**
     * @var    bool
     */
    protected $is_required = false;
    /**
     * @var    bool
     */
    protected $is_disabled = false;
    /**
     * This is the value contained in the input as displayed
     * client side.
     *
     * @var    mixed
     */
    protected $value = null;
    /**
     * This is an error on the input as displayed client side.
     *
     * @var    string|null
     */
    protected $error = null;
    /**
     * @var    string|null
     */
    private $name = null;
    /**
     * This is the current content of the input in the abstraction. This results by
     * applying the transformations and constraints to the value(s) (@see: operations)
     * Note that the content is only calculated by applying the withInput function.
     *
     * @var    Result|null
     */
    protected $content = null;
    /**
     * @var Transformation[]
     */
    private $operations;


    /**
     * Input constructor.
     *
     * @param DataFactory $data_factory
     * @param Factory $refinery
     * @param                       $label
     * @param                       $byline
     */
    public function __construct(
        DataFactory $data_factory,
        Factory $refinery,
        $label,
        $byline
    ) {
        $this->data_factory = $data_factory;
        $this->refinery = $refinery;
        $this->checkStringArg("label", $label);
        if ($byline !== null) {
            $this->checkStringArg("byline", $byline);
        }
        $this->label = $label;
        $this->byline = $byline;
        $this->operations = [];
    }

    // Observable properties of the input as it is shown to the client.


    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->label;
    }


    /**
     * @inheritdoc
     */
    public function withLabel($label)
    {
        $this->checkStringArg("label", $label);
        $clone = clone $this;
        $clone->label = $label;

        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function getByline()
    {
        return $this->byline;
    }


    /**
     * @inheritdoc
     */
    public function withByline($byline)
    {
        $this->checkStringArg("byline", $byline);
        $clone = clone $this;
        $clone->byline = $byline;

        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function isRequired()
    {
        return $this->is_required;
    }


    /**
     * @inheritdoc
     */
    public function withRequired($is_required)
    {
        $this->checkBoolArg("is_required", $is_required);
        $clone = clone $this;
        $clone->is_required = $is_required;

        return $clone;
    }


    /**
     * This may return a constraint that will be checked first if the field is
     * required.
     *
     * @return    Constraint|null
     */
    abstract protected function getConstraintForRequirement();


    /**
     * @inheritdoc
     */
    public function isDisabled()
    {
        return $this->is_disabled;
    }


    /**
     * @inheritdoc
     */
    public function withDisabled($is_disabled)
    {
        $this->checkBoolArg("is_disabled", $is_disabled);
        $clone = clone $this;
        $clone->is_disabled = $is_disabled;

        return $clone;
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
     * @param    mixed
     *
     * @throws  \InvalidArgumentException    if value does not fit client side input
     * @return Input
     */
    public function withValue($value)
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
     *
     * @return    bool
     */
    abstract protected function isClientSideValueOk($value) : bool;


    /**
     * The error of the input as used in HTML.
     *
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }


    /**
     * Get an input like this one, with a different error.
     *
     * @param    string
     *
     * @return    Input
     */
    public function withError($error)
    {
        $clone = clone $this;
        $clone->setError($error);

        return $clone;
    }


    /**
     * Set an error on this input.
     *
     * @param    string
     *
     * @return    void
     */
    private function setError($error)
    {
        $this->checkStringArg("error", $error);
        $this->error = $error;
    }

    /**
     * Apply a transformation to the current or future content.
     *
     * @param    Transformation $trafo
     *
     * @return    Input
     */
    public function withAdditionalTransformation(Transformation $trafo)
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
     *
     * @param    Transformation $trafo
     *
     * @return    void
     */
    protected function setAdditionalTransformation(Transformation $trafo)
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

    // Implementation of FormInputInternal

    // This is the machinery to be used to process the input from the client side.
    // This should not be exposed to the consumers of the inputs. These methods
    // should instead only be used by the forms wrapping the input.

    /**
     * @inheritdoc
     */
    final public function getName()
    {
        return $this->name;
    }


    /**
     * @inheritdoc
     */
    public function withNameFrom(NameSource $source)
    {
        $clone = clone $this;
        $clone->name = $source->getNewName();

        return $clone;
    }


    /**
     * Collects the input, applies trafos on the input and returns
     * a new input reflecting the data that was putted in.
     *
     * @inheritdoc
     */
    public function withInput(InputData $input)
    {
        if ($this->getName() === null) {
            throw new \LogicException("Can only collect if input has a name.");
        }

        //TODO: Discuss, is this correct here. If there is no input contained in this post
        //We assign null. Note that unset checkboxes are not contained in POST.
        if (!$this->isDisabled()) {
            $value = $input->getOr($this->getName(), null);
            // ATTENTION: There was a special case for the Filter Input Container here,
            // which lead to #27909. The issue will most certainly appear again in. If
            // you are the one debugging it and came here: Please don't put knowledge
            // of the special case for the filter in this general class. Have a look
            // into https://mantis.ilias.de/view.php?id=27909 for the according discussion.
            $clone = $this->withValue($value);
        } else {
            $clone = $this;
        }

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
     *
     * @return    Result
     */
    protected function applyOperationsTo($res)
    {
        if ($res === null && !$this->isRequired()) {
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


    /**
     * Get the operations that should be performed on the input.
     *
     * @return \Generator<Transformation>
     */
    private function getOperations()
    {
        if ($this->isRequired()) {
            $op = $this->getConstraintForRequirement();
            if ($op !== null) {
                yield $op;
            }
        }

        foreach ($this->operations as $op) {
            yield $op;
        }
    }


    /**
     * @inheritdoc
     */
    public function getContent()
    {
        if (is_null($this->content)) {
            throw new \LogicException("No content of this field has been evaluated yet. Seems withRequest was not called.");
        }
        return $this->content;
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
    public function appendOnUpdate(Signal $signal)
    {
        return $this->appendTriggeredSignal($signal, 'update');
    }
}
