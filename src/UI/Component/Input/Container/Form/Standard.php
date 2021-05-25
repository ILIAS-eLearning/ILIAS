<?php

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Form;

/**
 * This describes a standard form.
 */
interface Standard extends Form
{

    /**
     * Get the URL this form posts its result to.
     *
     * @return    string
     */
    public function getPostURL();

    /**
     * Sets the caption of the submit button of the form
     */
    public function withSubmitCaption(string $caption) : Standard;

    /**
     * Gets the submit caption of the form
     */
    public function getSubmitCaption() : ?string;
}
