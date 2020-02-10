<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Component\Component;
use ILIAS\Refinery\Transformation;
use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Component\OnUpdateable;

/**
 * This describes commonalities between all inputs.
 *
 * Inputs are different from other UI components. They bundle two things:
 * the displaying of the component (as the other components do as well)
 * and the processing of data as it is received from the client.
 *
 * There are two types of input fields, individual and groups. They share
 * this same basic input interface.
 *
 * When the the term "value" is used, it references the content of the input
 * as it is shown to the client. The term "content" on the other hand means
 * the value that is contained in the input after the client sends it to the
 * server.
 *
 * The latter, i.e. the content, can be validated via constraints and transformed
 * into other types of data. This means, that e.g. the value of an input could
 * be some id, while the content could be some object referenced by that id.
 */
interface Input
{
    /**
     * Get the value that is displayed in the input client side.
     *
     * @return    mixed
     */
    public function getValue();


    /**
     * Get an input like this with another value displayed on the
     * client side.
     *
     * @param    mixed
     *
     * @throws  \InvalidArgumentException    if value does not fit client side input
     * @return Input
     */
    public function withValue($value);


    /**
     * Apply a transformation to the content of the input.
     *
     * @param    Transformation $trafo
     *
     * @return    Input
     */
    public function withAdditionalTransformation(Transformation $trafo);
}
