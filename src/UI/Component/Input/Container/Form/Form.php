<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Form;

use ILIAS\UI\Component\Component;
use ILIAS\Transformation\Transformation;

use Psr\Http\Message\ServerRequestInterface;

/**
 * This describes commonalities between all forms.
 */
interface Form extends Component
{

    /**
     * Get the inputs contained in the form.
     *
     * @return    array<mixed,\ILIAS\UI\Component\Input\Input>
     */
    public function getInputs();


    /**
     * Get a form like this where data from the request is attached.
     *
     * @param    ServerRequestInterface $request
     *
     * @return    Form
     */
    public function withRequest(ServerRequestInterface $request);


    /**
     * Apply a transformation to the data of the form.
     *
     * @param    Transformation $trafo
     *
     * @return    Input
     */
    public function withAdditionalTransformation(Transformation $trafo);


    /**
     * Get the data in the form if all inputs are ok, where the transformation
     * is applied if one was added. If data was not ok, this will return null.
     *
     * @return    mixed|null
     */
    public function getData();
    /**
     * TODO: there should be a further method to attach the different submit buttons
     */
}
