<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Form;

/**
 * This describes a standard form.
 */
interface Standard extends Form
{
    /**
     * Get the URL this form posts its result to.
     */
    public function getPostURL() : string;

    /**
     * Sets the caption of the submit button of the form
     */
    public function withSubmitCaption(string $caption) : Standard;

    /**
     * Gets submit caption of the form
     */
    public function getSubmitCaption() : ?string;
}
