<?php declare(strict_types=1);

/* Copyright (c) 2021 Thibeau Fuhrer <thf@studer-raimann.ch> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component\Input\Field\Input as InputInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory;
use ILIAS\UI\Component as C;
use ILIAS\Refinery\Constraint;
use Closure;
use ilLanguage;

/**
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class File extends DynamicInputsAwareInput implements C\Input\Field\File
{
    // ===============================================
    // BEGIN IMPLEMENTATION OF FileUploadAware
    // ===============================================

    use FileUploadAwareHelper;

    public function __construct(
        ilLanguage $language,
        DataFactory $data_factory,
        Factory $refinery,
        C\Input\Field\UploadHandler $handler,
        string $label,
        ?string $byline
    ) {
        parent::__construct($language, $data_factory, $refinery, $label, $byline);

        $this->dynamic_input_template = new Hidden($data_factory, $refinery);
        $this->upload_handler = $handler;
        $this->value = [];
    }

    // ===============================================
    // END IMPLEMENTATION OF FileUploadAware
    // ===============================================

    // ===============================================
    // BEGIN OVERWRITTEN METHODS OF DynamicInputsAware
    // ===============================================

    /**
     * Merges the provided template with this inputs default one.
     */
    public function withTemplateForDynamicInputs(InputInterface $template) : DynamicInputsAwareInput
    {
        $clone = clone $this;
        $clone->has_metadata_inputs = true;
        $clone->dynamic_input_template = $this->mergeTemplateWithInput(
            $template,
            $clone->dynamic_input_template
        );

        return $clone;
    }

    /**
     * Maps generated dynamic inputs to their file-id, which must be
     * provided in or as $value.
     */
    public function withValue($value) : DynamicInputsAwareInput
    {
        $this->checkArg("value", $this->isClientSideValueOk($value), "Display value does not match input type.");

        $clone = clone $this;
        $identifier_key = $clone->upload_handler->getFileIdentifierParameterName();
        foreach ($value as $data) {
            $file_id = ($clone->hasMetadataInputs()) ? $data[$identifier_key] : $data;

            // that was not implicitly intended, but mapping dynamic inputs
            // to the file-id is also a duplicate protection.
            $clone->dynamic_inputs[$file_id] = $clone->dynamic_input_template->withValue($data);
        }

        return $clone;
    }

    // ===============================================
    // END OVERWRITTEN METHODS OF DynamicInputsAware
    // ===============================================

    public function hasMetadataInputs() : bool
    {
        return $this->has_metadata_inputs;
    }

    public function getUpdateOnLoadCode() : Closure
    {
        return static function () {
        };
    }

    protected function getConstraintForRequirement() : ?Constraint
    {
        return $this->refinery->to()->string();
    }

    protected function isClientSideValueOk($value) : bool
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $data) {
            // if no dynamic input template was provided, the values
            // must all be strings (possibly file-ids).
            if (!is_string($data) && !$this->hasMetadataInputs()) {
                return false;
            }

            if ($this->hasMetadataInputs()) {
                // if a dynamic input template was provided, the values
                // must all contain the file-id as an array entry.
                if (!array_key_exists($this->upload_handler->getFileIdentifierParameterName(), $data)) {
                    return false;
                }

                // if a dynamic input template was provided, the values
                // must be valid for the template input.
                if (!$this->dynamic_input_template->isClientSideValueOk($data)) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function mergeTemplateWithInput(InputInterface $template, InputInterface $input) : C\Input\Field\Group
    {
        $identifier_key = $this->upload_handler->getFileIdentifierParameterName();
        $inputs = ($template instanceof C\Input\Field\Group) ? $template->getInputs() : [$template];
        $inputs[$identifier_key] = $input;

        return new Group(
            $this->data_factory,
            $this->refinery,
            $this->language,
            $inputs,
            ''
        );
    }
}
