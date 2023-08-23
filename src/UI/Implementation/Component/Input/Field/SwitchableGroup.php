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

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Component\Input\Field as I;
use ilLanguage;
use LogicException;
use InvalidArgumentException;

/**
 * This implements the switchable group.
 */
class SwitchableGroup extends Group implements I\SwitchableGroup
{
    use JavaScriptBindable;
    use Triggerer;

    /**
     * Only adds a check to the original group-constructor.
     */
    public function __construct(
        DataFactory $data_factory,
        \ILIAS\Refinery\Factory $refinery,
        ilLanguage $lng,
        array $inputs,
        string $label,
        ?string $byline = null
    ) {
        $this->checkArgListElements('inputs', $inputs, [I\Group::class]);
        parent::__construct($data_factory, $refinery, $lng, $inputs, $label, $byline);
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement(): ?Constraint
    {
        if ($this->requirement_constraint !== null) {
            return $this->requirement_constraint;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function isClientSideValueOk($value): bool
    {
        if (!is_string($value) && !is_int($value)) {
            return false;
        }
        return array_key_exists($value, $this->getInputs());
    }

    public function withRequired($is_required, ?Constraint $requirement_constraint = null): self
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return FormInput::withRequired($is_required, $requirement_constraint);
    }

    /**
     * @inheritdoc
     */
    public function withValue($value): self
    {
        if (is_string($value) || is_int($value)) {
            /** @noinspection PhpIncompatibleReturnTypeInspection */
            return FormInput::withValue($value);
        }
        if (!is_array($value) || count($value) !== 2) {
            throw new InvalidArgumentException(
                "Expected one key and a group value or one key only as value."
                . " got '" . print_r($value, true) . "' instead."
            );
        }
        list($key, $group_value) = $value;

        /** @var $clone self */
        $clone = FormInput::withValue($key);
        $clone->setInputs($clone->getInputsWithOperationForKey($key, fn ($i) => $i->withValue($group_value)));
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        $key = FormInput::getValue();
        if (is_null($key)) {
            return null;
        }

        $input = $this->getInputs()[$key] ?? null;
        if (null === $input) {
            return null;
        }

        return [$key, $input->getValue()];
    }

    /**
     * @inheritdoc
     */
    public function withInput(InputData $input): self
    {
        if ($this->getName() === null) {
            throw new LogicException("Can only collect if input has a name.");
        }

        $key = $input->getOr($this->getName(), "");
        $clone = clone $this;

        if ($key === "") {
            if ($this->isRequired()) {
                $clone->content = $clone->data_factory->error($this->lng->txt("ui_error_switchable_group_required"));
                return $clone->withError("" . $clone->content->error());
            }

            $clone->content = $clone->data_factory->ok([$key, []]);
            return $clone;
        }

        if (!$this->isDisabled()) {
            $clone = $clone->withValue($key);
            $clone->setInputs($clone->getInputsWithOperationForKey($key, fn ($i) => $i->withInput($input)));
        }

        /** @var $inputs I\Group[] */
        $inputs = $clone->getInputs();
        if (!array_key_exists($key, $inputs)) {
            $clone->content = $clone->data_factory->ok([$key, []]);
            return $clone;
        }

        if ($inputs[$key]->getContent()->isError()) {
            $clone->content = $clone->data_factory->error($this->lng->txt("ui_error_in_group"));
            return $clone;
        }

        $contents = [];
        foreach ($inputs[$key]->getInputs() as $subkey => $group_input) {
            $content = $group_input->getContent();
            if ($content->isOK()) {
                $contents[$subkey] = $content->value();
            }
        }

        $clone->content = $this->applyOperationsTo([$key, $contents]);
        if ($clone->content->isError()) {
            return $clone->withError("" . $clone->content->error());
        }

        return $clone;
    }

    /**
     * Returns the inputs for @see Group::setInputs() with $operation applied to the input for
     * the given $key. The callable will recieve the input as its only argument and must return
     * it again with applied operations.
     */
    protected function getInputsWithOperationForKey($key, \Closure $operation): array
    {
        $this->checkArg("key", is_int($key) || is_string($key), "Key must be int or string.");
        $inputs = $this->getInputs();
        if (!array_key_exists($key, $inputs)) {
            throw new LogicException("Key '$key' does not exist in inputs.");
        }
        $inputs[$key] = $operation($inputs[$key]);
        return $inputs;
    }
}
