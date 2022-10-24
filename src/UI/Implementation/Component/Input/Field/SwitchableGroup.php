<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Constraint;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Component\Input\Field;
use ilLanguage;
use LogicException;
use InvalidArgumentException;

/**
 * This implements the switchable group.
 */
class SwitchableGroup extends Group implements Field\SwitchableGroup
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
        string $byline = null
    ) {
        $this->checkArgListElements("inputs", $inputs, Group::class);
        parent::__construct($data_factory, $refinery, $lng, $inputs, $label, $byline);
    }

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement(): ?Constraint
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value): bool
    {
        if (!is_string($value) && !is_int($value)) {
            return false;
        }
        return array_key_exists($value, $this->inputs);
    }

    public function withRequired($is_required): Field\Input
    {
        return Input::withRequired($is_required);
    }

    /**
     * @inheritdoc
     */
    public function withValue($value): Field\Input
    {
        if (is_string($value) || is_int($value)) {
            return Input::withValue($value);
        }
        if (!is_array($value) || count($value) !== 2) {
            throw new InvalidArgumentException(
                "Expected one key and a group value or one key only as value."
                . " got '" . print_r($value, true) . "' instead."
            );
        }
        list($key, $group_value) = $value;
        $clone = Input::withValue($key);
        $clone->inputs[$key] = $clone->inputs[$key]->withValue($group_value);
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        $key = Input::getValue();
        if (is_null($key)) {
            return null;
        }
        return [$key, $this->inputs[$key]->getValue()];
    }

    /**
     * @inheritdoc
     */
    public function withInput(InputData $input): Field\Input
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
            } else {
                $clone->content = $clone->data_factory->ok([$key, []]);
            }
            return $clone;
        }

        if (!$this->isDisabled()) {
            $clone = $clone->withValue($key);
            $clone->inputs[$key] = $clone->inputs[$key]->withInput($input);
        }

        if (array_key_exists($key, $clone->inputs) && $clone->inputs[$key]->getContent()->isError()) {
            $clone->content = $clone->data_factory->error($this->lng->txt("ui_error_in_group"));
        } else {
            $contents = [];
            $group_inputs = $clone->inputs[$key]->getInputs();

            foreach ($group_inputs as $subkey => $group_input) {
                $content = $group_input->getContent();
                if ($content->isOK()) {
                    $contents[$subkey] = $content->value();
                }
            }

            $clone->content = $this->applyOperationsTo([$key, $contents]);
            if ($clone->content->isError()) {
                return $clone->withError("" . $clone->content->error());
            }
        }

        return $clone;
    }
}
