<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Result;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\Input\PostData;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\Transformation\Transformation;
use ILIAS\Transformation\Factory as TransformationFactory;
use ILIAS\Validation\Constraint;
use ILIAS\Validation\Factory as ValidationFactory;

/**
 * This implements commonalities between inputs.
 */
abstract class Input implements C\Input\Field\Input, InputInternal
{
    use ComponentHelper;
    /**
     * @var DataFactory
     */
    protected $data_factory;
    /**
     * @var    ValidationFactory
     */
    protected $validation_factory;
    /**
     * @var TransformationFactory
     */
    protected $transformation_factory;
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
     * @var (Transformation|Constraint)[]
     */
    private $operations;


    /**
     * Input constructor.
     *
     * @param DataFactory           $data_factory
     * @param ValidationFactory     $validation_factory
     * @param TransformationFactory $transformation_factory
     * @param                       $label
     * @param                       $byline
     */
    public function __construct(
        DataFactory $data_factory,
        ValidationFactory $validation_factory,
        TransformationFactory $transformation_factory,
        $label,
        $byline
    ) {
        $this->data_factory = $data_factory;
        $this->validation_factory = $validation_factory;
        $this->transformation_factory = $transformation_factory;
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
     * @param    mixed $value
     *
     * @return    bool
     */
    abstract protected function isClientSideValueOk($value);


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

    // These are the ways in which a consumer can define how client side
    // input is processed.

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
            $this->content = $this->content->map($trafo);
        }
    }


    /**
     * Apply a constraint to the current or the future content.
     *
     * @param    Constraint $constraint
     *
     * @return    Input
     */
    public function withAdditionalConstraint(Constraint $constraint)
    {
        $clone = clone $this;
        $clone->setAdditionalConstraint($constraint);

        return $clone;
    }


    /**
     * Apply a constraint to the current or the future content.
     *
     * ATTENTION: This is a real setter, i.e. it modifies $this! Use this only if
     * `withAdditionalConstraint` does not work, i.e. in the constructor.
     *
     * @param    Constraint $constraint
     *
     * @return    void
     */
    protected function setAdditionalConstraint(Constraint $constraint)
    {
        $this->operations[] = $constraint;
        if ($this->content !== null) {
            $this->content = $constraint->restrict($this->content);
            if ($this->content->isError()) {
                $this->setError("" . $this->content->error());
            }
        }
    }

    // Implementation of InputInternal

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
    public function withInput(PostData $input)
    {
        //TODO: What should happen if input has not name? Throw exception or return null?
        /**
         * if ($this->getName() === null) {
         * throw new \LogicException("Can only collect if input has a name.");
         * }**/

        //TODO: Discuss, is this correct here. If there is no input contained in this post
        //We assign null. Note that unset checkboxes are not contained in POST.
        $value = $input->getOr($this->getName(), null);
        $clone = $this->withValue($value);
        $clone->content = $this->applyOperationsTo($value);
        if ($clone->content->isError()) {
            return $clone->withError("" . $clone->content->error());
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

            // TODO: I could make this go away by giving Transformation and
            // Constraint a common interface for that.
            if ($op instanceof Transformation) {
                $res = $res->map($op);
            } elseif ($op instanceof Constraint) {
                $res = $op->restrict($res);
            }
        }

        return $res;
    }


    /**
     * Get the operations that should be performed on the input.
     *
     * @return \Generator <Transformation|Constraint>
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
    final public function getContent()
    {
        if (is_null($this->content)) {
            throw new \LogicException("No content of this field has been evaluated yet. Seems withInput was not called.");
        }
        return $this->content;
    }
}
