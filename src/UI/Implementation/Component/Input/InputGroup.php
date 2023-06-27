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

trait InputGroup
{
    /**
     * Inputs that are contained by this group
     *
     * @var    Input[]
     */
    protected array $inputs = [];

    public function withDisabled(bool $is_disabled): self
    {
        $clone = parent::withDisabled($is_disabled);
        $clone->inputs = array_map(fn ($i) => $i->withDisabled($is_disabled), $this->inputs);
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function isClientSideValueOk($value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        if (count($this->getInputs()) !== count($value)) {
            return false;
        }
        foreach ($this->getInputs() as $key => $input) {
            if (!array_key_exists($key, $value)) {
                return false;
            }
            if (!$input->isClientSideValueOk($value[$key])) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the value that is displayed in the input client side.
     *
     * @return    mixed
     */
    public function getValue()
    {
        return array_map(fn ($i) => $i->getValue(), $this->inputs);
    }


    /**
     * Get an input like this with another value displayed on the
     * client side.
     *
     * @param   mixed
     * @throws  InvalidArgumentException    if value does not fit client side input
     */
    public function withValue($value): self
    {
        $this->checkArg("value", $this->isClientSideValueOk($value), "Display value does not match input type.");
        $clone = clone $this;
        foreach ($this->inputs as $k => $i) {
            $clone->inputs[$k] = $i->withValue($value[$k]);
        }
        return $clone;
    }

    /**
     * Collects the input, applies trafos and forwards the input to its children and returns
     * a new input group reflecting the inputs with data that was put in.
     *
     * @inheritdoc
     */
    public function withInput(InputData $input): self
    {
        if (sizeof($this->getInputs()) === 0) {
            return $this;
        }

        $clone = clone $this;

        $inputs = [];
        $contents = [];
        $error = false;

        foreach ($this->getInputs() as $key => $in) {
            $inputs[$key] = $in->withInput($input);
            $content = $inputs[$key]->getContent();
            if ($content->isError()) {
                $error = true;
            } else {
                $contents[$key] = $content->value();
            }
        }

        $clone->inputs = $inputs;
        if ($error) {
            $clone->content = $clone->data_factory->error($this->lng->txt("ui_error_in_group"));
        } else {
            $clone->content = $clone->applyOperationsTo($contents);
        }

        if ($clone->content->isError()) {
            $clone = $clone->withError("" . $clone->content->error());
        }

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function withNameFrom(NameSource $source, ?string $parent_name = null): self
    {
        $clone = parent::withNameFrom($source, $parent_name);

        /**
         * @var $clone Group
         */
        $named_inputs = [];
        foreach ($this->getInputs() as $key => $input) {
            $named_inputs[$key] = $input->withNameFrom($source, $clone->getName());
        }

        $clone->inputs = $named_inputs;

        return $clone;
    }

    /**
     * @return Input[]
     */
    public function getInputs(): array
    {
        return $this->inputs;
    }
}
