<?php

namespace ILIAS\UI\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\NameSource;

/**
 * Interface AdditionalInputsAware
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 * @package ILIAS\UI\Component\Input\Field
 */
interface AdditionalInputsAware
{
    /**
     * Returns an input like this, with a template for additional sub inputs.
     *
     * @param Input $input
     * @return AdditionalInputsAware
     */
    public function withTemplateForAdditionalInputs(Input $input) : AdditionalInputsAware;

    /**
     * Returns the input template for additional sub inputs.
     *
     * @return Input|null
     */
    public function getTemplateForAdditionalInputs() : ?Input;

    /**
     * Returns all prepared input templates for additional sub inputs.
     *
     * @return Input[]|null
     */
    public function getPreparedTemplatesForAdditionalInputs() : ?array;
}