<?php

/* Copyright (c) 2017 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see
docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component as C;
use ILIAS\Validation\Factory as ValidationFactory;
use ILIAS\Transformation\Factory as TransformationFactory;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\Input\PostData;

/**
 * This implements the checkbox input, note that this uses GroupHelper to manage potentially
 * attached dependant groups.
 */
class Checkbox extends Input implements C\Input\Field\Checkbox, C\Changeable, C\Onloadable
{
    use JavaScriptBindable;
    use Triggerer;
    use DependantGroupHelper;


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
    protected function isClientSideValueOk($value)
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
        //be lenient to bool params for easier use
        if ($value === true) {
            $value = "checked";
        } else {
            if ($value === false) {
                $value = "";
            }
        }

        return parent::withValue($value);
    }


    /**
     * @inheritdoc
     */
    public function withInput(PostData $post_input)
    {
        if ($this->getName() === null) {
            throw new \LogicException("Can only collect if input has a name.");
        }

        $value = $post_input->getOr($this->getName(), "");
        $clone = $this->withValue($value);
        $clone->content = $this->applyOperationsTo($value);
        if ($clone->content->isError()) {
            return $clone->withError("" . $clone->content->error());
        }

        $clone = $clone->withGroupInput($post_input);

        return $clone;
    }
}
