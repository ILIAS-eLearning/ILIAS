<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\Input\InputData;

/**
 * This implements the checkbox input.
 */
class Checkbox extends Input implements C\Input\Field\Checkbox, C\Changeable, C\Onloadable
{
    use JavaScriptBindable;
    use Triggerer;

    /**
     * @inheritdoc
     */
    protected function getConstraintForRequirement()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value) : bool
    {
        if ($value == "checked" || $value === "" || is_bool($value)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * @inheritdoc
     * @return Checkbox
     */
    public function withValue($value)
    {
        $value = $value ?? false;

        if (!is_bool($value)) {
            throw new \InvalidArgumentException(
                "Unknown value type for checkbox: " . gettype($value)
            );
        }

        /**
         * @var $clone Checkbox
         */
        $clone = parent::withValue($value);
        return $clone;
    }


    /**
     * @inheritdoc
     */
    public function withInput(InputData $post_input)
    {
        if ($this->getName() === null) {
            throw new \LogicException("Can only collect if input has a name.");
        }

        if (!$this->isDisabled()) {
            $value = $post_input->getOr($this->getName(), "");
            $clone = $this->withValue($value === "checked");
        } else {
            $value = $this->getValue();
            $clone = $this;
        }

        $clone->content = $this->applyOperationsTo($clone->getValue());
        if ($clone->content->isError()) {
            return $clone->withError("" . $clone->content->error());
        }

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function appendOnLoad(C\Signal $signal)
    {
        return $this->appendTriggeredSignal($signal, 'load');
    }

    /**
     * @inheritdoc
     */
    public function withOnChange(C\Signal $signal)
    {
        return $this->withTriggeredSignal($signal, 'change');
    }

    /**
     * @inheritdoc
     */
    public function appendOnChange(C\Signal $signal)
    {
        return $this->appendTriggeredSignal($signal, 'change');
    }

    /**
     * @inheritdoc
     */
    public function withOnLoad(C\Signal $signal)
    {
        return $this->withTriggeredSignal($signal, 'load');
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode() : \Closure
    {
        return function ($id) {
            $code = "$('#$id').on('input', function(event) {
			il.UI.input.onFieldUpdate(event, '$id', $('#$id').prop('checked').toString());
		});
		il.UI.input.onFieldUpdate(event, '$id', $('#$id').prop('checked').toString());";
            return $code;
        };
    }
}
