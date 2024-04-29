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
 */

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Input;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Refinery\Constraint;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Result;
use ILIAS\Data\Result\Ok;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
trait Group
{
    use ComponentHelper;

    /**
     * @var \ILIAS\UI\Component\Input\Input[]
     */
    protected array $inputs = [];

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
     * @param mixed $value
     * @throws  \InvalidArgumentException    if value does not fit client side input
     */
    public function withValue($value): \ILIAS\UI\Implementation\Component\Input\Input
    {
        $this->checkArg("value", $this->isClientSideValueOk($value), "Display value does not match input type.");
        $clone = clone $this;
        foreach ($this->getInputs() as $k => $i) {
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
    public function withInput(InputData $input): \ILIAS\UI\Implementation\Component\Input\Input
    {
        if (empty($this->getInputs())) {
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
            $clone->content = $clone->getDataFactory()->error($this->getLanguage()->txt("ui_error_in_group"));
        } else {
            $clone->content = $clone->applyOperationsTo($contents);
        }

        if ($clone->content->isError()) {
            $clone->setError("" . $clone->content->error());
        }

        return $clone;
    }

    /**
     * @return Input[]
     */
    protected function nameInputs(NameSource $source, string $parent_name): array
    {
        $named_inputs = [];
        foreach ($this->getInputs() as $key => $input) {
            $named_inputs[$key] = $input->withNameFrom($source, $parent_name);
        }

        return $named_inputs;
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
     * @return Input[]
     */
    public function getInputs(): array
    {
        return $this->inputs;
    }

    /**
     * This setter should be used instead of accessing $this->inputs directly.
     *
     * @param \ILIAS\UI\Component\Input\Input[] $inputs
     */
    protected function setInputs(array $inputs): void
    {
        $this->inputs = $inputs;
    }

    /**
     * @see Input::applyOperationsTo()
     */
    abstract protected function applyOperationsTo($res): Result;

    /**
     * This setter will be used by withInput() to set possible errors.
     */
    abstract protected function setError(string $error): void;

    abstract protected function getLanguage(): \ilLanguage;

    abstract protected function getDataFactory(): DataFactory;
}
