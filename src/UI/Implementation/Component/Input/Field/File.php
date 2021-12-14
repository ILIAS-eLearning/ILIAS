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
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\InputData;
use LogicException;
use ILIAS\Data\Result\Ok;
use ILIAS\UI\Implementation\Component\Input\Container\Form\ArrayInputData;
use ILIAS\UI\Implementation\Component\Input\DynamicInputsTemplateNameSource;

/**
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 */
class File extends DynamicInputsAwareInput implements C\Input\Field\File
{
    public const KEY_ZIP_EXTRACT = 'file_zip_extract';
    public const KEY_ZIP_STRUCTURE = 'file_zip_structure';

    protected C\Input\Field\Group $zip_options;
    protected C\Input\Field\UploadHandler $upload_handler;
    protected array $accepted_mime_types = [];
    protected bool $has_zip_options = false;
    protected int $max_file_amount = 1;
    protected int $max_file_size = 2048;

    public function __construct(
        ilLanguage $language,
        DataFactory $data_factory,
        Factory $refinery,
        C\Input\Field\UploadHandler $handler,
        string $label,
        ?string $byline,
        bool $has_zip_options = false
    ) {
        parent::__construct($language, $data_factory, $refinery, $label, $byline);

        $this->value = [];
        $this->upload_handler = $handler;
        $this->has_zip_options = $has_zip_options;
        $this->zip_options = $this->getGroup([
            self::KEY_ZIP_EXTRACT => $this->getCheckbox($language->txt('file_zip_extract')),
            self::KEY_ZIP_STRUCTURE => $this->getCheckbox($language->txt('file_zip_structure')),
        ]);
    }

    public function getUploadHandler() : C\Input\Field\UploadHandler
    {
        return $this->upload_handler;
    }

    public function withMaxFileSize(int $size_in_bytes) : C\Input\Field\File
    {
        $clone = clone $this;
        $clone->max_file_size = $size_in_bytes;

        return $clone;
    }

    public function getMaxFileSize() : int
    {
        return $this->max_file_size;
    }

    public function withMaxFiles(int $max_file_amount) : C\Input\Field\File
    {
        $clone = clone $this;
        $clone->max_file_amount = $max_file_amount;

        return $clone;
    }

    public function getMaxFiles() : int
    {
        return $this->max_file_amount;
    }

    public function withAcceptedMimeTypes(array $mime_types) : C\Input\Field\File
    {
        $clone = clone $this;
        $clone->accepted_mime_types = $mime_types;

        return $clone;
    }

    public function getAcceptedMimeTypes() : array
    {
        return $this->accepted_mime_types;
    }

    public function hasZipOptions() : bool
    {
        return $this->has_zip_options;
    }

    public function getZipOptions() : C\Input\Field\Group
    {
        return $this->zip_options;
    }

    public function withValue($value) : DynamicInputsAwareInput
    {
        /** @var $value FileUploadData[] */
        $this->checkArgListElements('value', $value, FileUploadData::class);
        $clone = clone $this;

        foreach ($value as $file_info) {
            $clone->value[$file_info->getFileId()] = $file_info;

            if ($clone->hasZipOptions() &&
                null !== $clone->getTemplateForDynamicInputs() &&
                'application/zip' === $file_info->getMimeType()
            ) {
                $dynamic_input = $clone->getGroup([
                    $clone->getZipOptions(),
                    $clone->getTemplateForDynamicInputs(),
                ]);

                $clone->dynamic_inputs[$file_info->getFileId()] = $dynamic_input->withValue(
                    $file_info->getMetadata(true)
                );
            }

            if ((null !== $clone->getTemplateForDynamicInputs())) {
                $dynamic_input = $clone->getTemplateForDynamicInputs();
                $clone->dynamic_inputs[$file_info->getFileId()] = $dynamic_input->withValue(
                    $file_info->getMetadata()
                );
            }
        }

        return $clone;
    }

    public function withInput(InputData $post_data) : DynamicInputsAwareInput
    {
        if (null === $this->getName()) {
            throw new LogicException(self::class . '::withNameFrom must be called first.');
        }

        $clone = clone $this;
        $post_data = $post_data->getOr($clone->getName(), null);
        $template = $clone->getTemplateForDynamicInputs();
        $contains_error = false;
        $contents = [];

        if (null === $post_data || null === $template) {
            $clone->content = $clone->applyOperationsTo(new Ok(null));
            if ($clone->content->isError()) {
                $clone = $clone->withError((string) $clone->content->error());
            }

            return $clone;
        }

        $t = $this->overridePostInputNames($post_data, $clone->getName());

        foreach ($this->overridePostInputNames($post_data, $clone->getName()) as $index => $input_data) {
            $result = $template->withInput(new ArrayInputData($input_data))->getContent();
            if ($result->isOk()) {
                $content = $result->value();
                // keeps the content mapped to the input name, if e.g. a group
                // or inputs with multiple values are the provided template.
                if (is_array($content)) {
                    foreach ($content as $key => $val) {
                        $contents[$index][$key] = $val;
                    }
                } else {
                    $contents[] = $content;
                }
            } else {
                $contains_error = true;
            }
        }

        if ($contains_error) {
            $clone->content = $clone->data_factory->error($this->language->txt("ui_error_in_group"));
        } else {
            $clone->content = $clone->applyOperationsTo($contents);
        }

        if ($clone->content->isError()) {
            $clone = $clone->withError((string) $clone->content->error());
        }

        return $clone;
    }

    public function withNameFrom(NameSource $source) : DynamicInputsAwareInput
    {
        $has_template = null !== $this->dynamic_input_template;
        if ($has_template && $this->hasZipOptions()) {
            $this->dynamic_input_template = $this->getGroup([
                $this->getZipOptions(),
                $this->dynamic_input_template,
            ]);
        }

        if (!$has_template && $this->hasZipOptions()) {
            $this->dynamic_input_template = $this->getZipOptions();
        }

        return parent::withNameFrom($source);
    }

    /**
     * @return FileUploadData[]
     */
    public function getValue() : array
    {
        return $this->value;
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

        foreach ($value as $key => $val) {
            if (!is_string($key) && null !== $this->getTemplateForDynamicInputs()) {
                return false;
            }

            if (!is_string($val) && null === $this->getTemplateForDynamicInputs()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param C\Input\Field\Input[] $inputs
     */
    private function getGroup(array $inputs) : C\Input\Field\Group
    {
        return new Group(
            $this->data_factory,
            $this->refinery,
            $this->language,
            $inputs,
            ''
        );
    }

    private function getCheckbox(string $label) : C\Input\Field\Checkbox
    {
        return new Checkbox(
            $this->data_factory,
            $this->refinery,
            $label,
            null
        );
    }
}
