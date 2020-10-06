<?php declare(strict_types=1);

/* Copyright (c) 2017 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Input\Container\Form;

use ILIAS\Data\URI;

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
     * Set the URL that is called when the user clicks 'cancel'.
     * Setting an cancel-url will enable an accroding button in the form.
     *
     * @return    Standard
     */
    public function withCancelURL(URI $url) : Standard;

    /**
     * Get the URL that is called when the user clicks 'cancel'.
     */
    public function getCancelURL() : ?URI;

    /**
     * Labels the submit-button of the form.
     *
     * @return    Standard
     */
    public function withSubmitLabel(string $label) : Standard;

    /**
     * Get the label of the submit-button.
     */
    public function getSubmitLabel() : string;

    /**
     * Do not render buttons at the top of the form.
     */
    public function withBottomButtonsOnly(bool $flag = true) : Standard;

    /**
     * Should the top-buttons be rendered?
     * Defaults to false.
     */
    public function hasBottomButtonsOnly() : bool;
}
