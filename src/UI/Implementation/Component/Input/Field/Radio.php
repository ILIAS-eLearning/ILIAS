<?php

/* Copyright (c) 2018 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component as C;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;

/**
 * This implements the radio input.
 */
class Radio extends Input implements C\Input\Field\Radio
{
    use JavaScriptBindable;
    use Triggerer;

    /**
     * @var array <string,string> {$value => $label}
     */
    protected $options = [];

    /**
     * @var array <string,array> {$option_value => $bylines}
     */
    protected $bylines = [];

    /**
     * @inheritdoc
     */
    protected function isClientSideValueOk($value)
    {
        return ($value === '' || array_key_exists($value, $this->getOptions()));
    }

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
    public function withOption(string $value, string $label, string $byline=null) : C\Input\Field\Radio
    {
        $clone = clone $this;
        $clone->options[$value] = $label;
        if (!is_null($byline)) {
            $clone->bylines[$value] = $byline;
        }
        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getOptions() : array
    {
        return $this->options;
    }


    public function getBylineFor(string $value)
    {
        if (!array_key_exists($value, $this->bylines)) {
            return null;
        }
        return $this->bylines[$value];
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
            $clone = $this->withValue($value);
        } else {
            $value = $this->getValue();
            $clone = $this;
        }

        $clone->content = $this->applyOperationsTo($value);
        if ($clone->content->isError()) {
            return $clone->withError("" . $clone->content->error());
        }

        $clone->content = $this->applyOperationsTo($value);

        if ($clone->getError()) {
            $clone->content = $clone->data_factory->error($clone->getError());
        }

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getUpdateOnLoadCode() : \Closure
    {
        return function ($id) {
            $code = "$('#$id').on('input', function(event) {
				il.UI.input.onFieldUpdate(event, '$id', $('#$id input:checked').val());
			});
			il.UI.input.onFieldUpdate(event, '$id', $('#$id input:checked').val());";
            return $code;
        };
    }
}
