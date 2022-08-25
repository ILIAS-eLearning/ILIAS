<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Component\Input\Field\Input as InputInterface;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component as C;
use ILIAS\Refinery\Constraint;
use Closure;
use ilLanguage;
use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;

/**
 * Class File
 * @package ILIAS\UI\Implementation\Component\Input\Field
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class File extends HasDynamicInputsBase implements C\Input\Field\File
{
    // ===============================================
    // BEGIN IMPLEMENTATION OF FileUpload
    // ===============================================

    use FileUploadHelper;

    public function __construct(
        ilLanguage $language,
        DataFactory $data_factory,
        Refinery $refinery,
        UploadLimitResolver $upload_limit_resolver,
        C\Input\Field\UploadHandler $handler,
        string $label,
        ?InputInterface $metadata_input,
        ?string $byline
    ) {
        $this->upload_limit_resolver = $upload_limit_resolver;
        $this->language = $language;
        $this->data_factory = $data_factory;
        $this->refinery = $refinery;
        $this->upload_handler = $handler;
        $this->value = [];

        parent::__construct(
            $language,
            $data_factory,
            $refinery,
            $label,
            $this->createDynamicInputsTemplate($metadata_input),
            $byline
        );
    }

    // ===============================================
    // END IMPLEMENTATION OF FileUpload
    // ===============================================

    // ===============================================
    // BEGIN OVERWRITTEN METHODS OF HasDynamicInputs
    // ===============================================

    /**
     * Maps generated dynamic inputs to their file-id, which must be
     * provided in or as $value.
     */
    public function withValue($value): HasDynamicInputsBase
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
    // END OVERWRITTEN METHODS OF HasDynamicInputs
    // ===============================================

    public function hasMetadataInputs(): bool
    {
        return $this->has_metadata_inputs;
    }

    /**
     * @return array<string, string>
     */
    public function getTranslations(): array
    {
        return [
            'invalid_mime' => $this->language->txt('ui_file_input_invalid_mime'),
            'invalid_size' => $this->language->txt('ui_file_input_invalid_size'),
            'invalid_amount' => $this->language->txt('ui_file_input_invalid_amount'),
            'general_error' => $this->language->txt('ui_file_input_general_error'),
        ];
    }

    public function getUpdateOnLoadCode(): Closure
    {
        return static function () {
        };
    }

    protected function getConstraintForRequirement(): ?Constraint
    {
        return $this->refinery->to()->string();
    }

    protected function isClientSideValueOk($value): bool
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

    protected function createDynamicInputsTemplate(?InputInterface $metadata_input): InputInterface
    {
        $default_metadata_input = new Hidden(
            $this->data_factory,
            $this->refinery
        );

        if (null === $metadata_input) {
            return $default_metadata_input;
        }

        $inputs = ($metadata_input instanceof C\Input\Field\Group) ?
            $metadata_input->getInputs() : [
                $metadata_input,
            ];

        // map the file-id input to the UploadHandlers identifier key.
        $inputs[$this->upload_handler->getFileIdentifierParameterName()] = $default_metadata_input;

        // tell the input that it contains actual metadata inputs.
        $this->has_metadata_inputs = true;

        return new Group(
            $this->data_factory,
            $this->refinery,
            $this->language,
            $inputs,
            ''
        );
    }
}
