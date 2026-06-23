<?php

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

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\UploadLimitResolver;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Component\Input\Field\FileUpload;
use ILIAS\UI\Component\Input\Container\Form\FormInput;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\UI\Component as C;
use ILIAS\Refinery\Constraint;
use Closure;
use ILIAS\Language\Language;

/**
 * Class File
 * @package ILIAS\UI\Implementation\Component\Input\Field
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class File extends HasDynamicInputs implements C\Input\Field\File
{
    // ===============================================
    // BEGIN IMPLEMENTATION OF FileUpload
    // ===============================================

    protected UploadLimitResolver $upload_limit_resolver;
    protected UploadHandler $upload_handler;
    protected array $accepted_mime_types = [];
    protected bool $has_metadata_inputs = false;
    protected int $max_file_amount = 1;
    protected int $max_file_size_in_bytes;

    public function __construct(
        Language $language,
        DataFactory $data_factory,
        Factory $field_factory,
        Refinery $refinery,
        UploadLimitResolver $upload_limit_resolver,
        C\Input\Field\UploadHandler $handler,
        string $label,
        ?FormInput $metadata_input,
        ?string $byline
    ) {
        $this->upload_limit_resolver = $upload_limit_resolver;
        $this->max_file_size_in_bytes = $upload_limit_resolver->getBestPossibleUploadLimitInBytes($handler);
        $this->upload_handler = $handler;
        $this->value = [];

        parent::__construct(
            $language,
            $data_factory,
            $refinery,
            $this->createDynamicInputsTemplate($field_factory, $metadata_input),
            $label,
            $byline,
        );
    }

    public function getUploadHandler(): UploadHandler
    {
        return $this->upload_handler;
    }

    public function withMaxFileSize(int $size_in_bytes): FileUpload
    {
        $clone = clone $this;
        $clone->max_file_size_in_bytes = $clone->upload_limit_resolver->getBestPossibleUploadLimitInBytes(
            $clone->upload_handler,
            $size_in_bytes
        );

        return $clone;
    }

    public function getMaxFileSize(): int
    {
        return $this->max_file_size_in_bytes;
    }

    public function withMaxFiles(int $max_file_amount): FileUpload
    {
        $clone = clone $this;
        $clone->max_file_amount = $max_file_amount;

        return $clone;
    }

    public function getMaxFiles(): int
    {
        return $this->max_file_amount;
    }

    public function withAcceptedMimeTypes(array $mime_types): FileUpload
    {
        $clone = clone $this;
        $clone->accepted_mime_types = $mime_types;

        return $clone;
    }

    public function getAcceptedMimeTypes(): array
    {
        return $this->accepted_mime_types;
    }

    // ===============================================
    // END IMPLEMENTATION OF FileUpload
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
        if ($this->requirement_constraint !== null) {
            return $this->requirement_constraint;
        }

        return $this->refinery->custom()->constraint(
            function ($value) {
                return (is_array($value) && count($value) > 0);
            },
            function ($txt, $value) {
                return $txt("msg_no_files_selected");
            },
        );
    }

    protected function isClientSideValueOk($value): bool
    {
        if (!is_array($value)) {
            return false;
        }
        foreach ($value as $data) {
            if (!$this->hasMetadataInputs() && !is_string($data)) {
                return false;
            }
            if ($this->hasMetadataInputs() && !is_string($data[0])) {
                return false;
            }
        }
        return true;
    }

    protected function createDynamicInputsTemplate(Factory $field_factory, ?FormInput $metadata_input): FormInput
    {
        $file_id_input = $field_factory->hidden();

        if (null === $metadata_input) {
            return $file_id_input;
        }

        $this->has_metadata_inputs = true;

        return $field_factory->group([$file_id_input, $metadata_input]);
    }
}
