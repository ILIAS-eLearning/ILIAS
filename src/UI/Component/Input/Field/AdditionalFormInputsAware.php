<?php

namespace ILIAS\UI\Component\Input\Field;

/**
 * Interface AdditionalFormInputsAware
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @package ILIAS\UI\Component\Input\Field
 */
interface AdditionalFormInputsAware
{
    /**
     * Returns an input like this, with a template for additional sub inputs.
     *
     * @param FormInput $template
     * @return AdditionalFormInputsAware
     */
    public function withTemplateForAdditionalInputs(FormInput $template) : AdditionalFormInputsAware;

    /**
     * Returns the input template for additional sub inputs.
     *
     * @return FormInput|null
     */
    public function getTemplateForAdditionalInputs() : ?FormInput;

    /**
     * Returns the additional inputs, generated from the template.
     *
     * @return FormInput[]|null
     */
    public function getAdditionalInputs() : ?array;
}