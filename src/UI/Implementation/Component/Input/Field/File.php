<?php

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Component\Input\Field\FileInput;
use ILIAS\Data\Result\Ok;
use ILIAS\UI\Implementation\Component\Input\SubordinateNameSource;
use ILIAS\FileUpload\Handler\FileInfoResult;

/**
 * Class File
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Input\Field
 */
class File extends Input implements C\Input\Field\FileInput
{
    use JavaScriptBindable;
    use Triggerer;

    /**
     * @var \ilLanguage
     */
    private $lang;

    /**
     * @var \ILIAS\UI\Implementation\Component\Input\Field\Factory
     */
    private $factory;

    /**
     * @var C\Input\Field\UploadHandler
     */
    private $upload_handler;

    /**
     * @var string[]
     */
    private $accepted_mime_types;

    /**
     * @var int|null
     */
    private $max_file_size;

    /**
     * @var int|null
     */
    private $max_files;

    /**
     * Holds the template for additional inputs.
     *
     * @var Input
     */
    private $template;

    /**
     * Holds the additional inputs, cloned from $this->template.
     *
     * @var Input[]
     */
    private $templates;

    /**
     * @var NameSource
     */
    private $name_source;

    /**
     * @var bool
     */
    private $zip_options = false;

    /**
     * File Constructor
     *
     * @param \ILIAS\UI\Implementation\Component\Input\Field\Factory $factory
     * @param DataFactory                                            $data_factory
     * @param \ilLanguage                                            $lang
     * @param Factory                                                $refinery
     * @param C\Input\Field\UploadHandler                            $handler
     * @param string                                                 $label
     * @param string|null                                            $byline
     * @param bool                                                   $with_zip_options
     */
    public function __construct(
        \ILIAS\UI\Implementation\Component\Input\Field\Factory $factory,
        DataFactory $data_factory,
        \ilLanguage $lang,
        Factory $refinery,
        C\Input\Field\UploadHandler $handler,
        string $label,
        string $byline = null,
        bool $with_zip_options = false
    ) {
        $this->lang = $lang;
        $this->upload_handler = $handler;
        $this->factory = $factory;
        $this->zip_options = $with_zip_options;

        parent::__construct($data_factory, $refinery, $label, $byline);
    }

    /**
     * @inheritDoc
     */
    public function getUploadHandler() : C\Input\Field\UploadHandler
    {
        return $this->upload_handler;
    }

    /**
     * @inheritDoc
     */
    public function withAcceptedMimeTypes(array $mime_types) : C\Input\Field\File
    {
        $clone = clone $this;
        $clone->accepted_mime_types = $mime_types;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getAcceptedMimeTypes() : ?array
    {
        return $this->accepted_mime_types;
    }

    /**
     * @inheritDoc
     */
    public function withMaxFileSize(int $size_in_bytes) : C\Input\Field\File
    {
        $clone = clone $this;
        $clone->max_file_size = $size_in_bytes;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getMaxFileSize() : ?int
    {
        return $this->max_file_size;
    }

    /**
     * @inheritDoc
     */
    public function withMaxFiles(int $amount) : C\Input\Field\File
    {
        $this->checkIntArg('amount', $amount);

        $clone = clone $this;
        $clone->max_files = $amount;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getMaxFiles() : int
    {
        if (null !== $this->max_files && 1 < $this->max_files) {
            return $this->max_files;
        }

        return 1;
    }

    /**
     * @inheritDoc
     */
    public function withZipExtractOptions(bool $with_options) : FileInput
    {
        $clone = clone $this;
        $clone->zip_options = $with_options;

        if (null !== $this->template && $with_options) {
            $clone->template = $this->mergeTemplateAndZipOptions($clone->template);
        }

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function hasZipExtractOptions() : bool
    {
        return $this->zip_options;
    }

    /**
     * @inheritDoc
     */
    public function withTemplateForAdditionalInputs(C\Input\Field\Input $input) : C\Input\Field\AdditionalInputsAware
    {
        $this->checkArgInstanceOf('input', $input, Input::class);

        $clone = clone $this;
        if (!$this->zip_options) {
            $clone->template = $input;
        } else {
            $clone->template = $this->mergeTemplateAndZipOptions($input);
        }

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getTemplateForAdditionalInputs() : ?C\Input\Field\Input
    {
        return $this->template;
    }

    /**
     * @inheritDoc
     */
    public function getPreparedTemplatesForAdditionalInputs() : ?array
    {
        return $this->templates;
    }

    /**
     * @inheritDoc
     */
    public function withValue($value)
    {
        // @TODO: doesnt work because of order!!

        $this->checkArg("value", $this->isClientSideValueOk($value), "Display value does not match input type.");

        $clone = clone $this;

        foreach ($value as $file_id => $template_value) {
            $file_info = $this->upload_handler->getInfoResult($file_id);
            if (null === $file_info) {
                throw new \InvalidArgumentException("Invalid argument supplied, '$file_id' is not an existing file.");
            }

            if (null !== $this->template) {
                $template = $clone->template->withValue($value);
                if ('application/zip' === $file_info->getMimeType()) {
                    $template = $this->mergeTemplateAndZipOptions($template);
                }

                $clone->templates[$file_id] = $template;
            }

            $clone->value[$file_id] = $file_info;
        }

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getValue()
    {
        if (null === $this->value) {
            return null;
        }

        $values = [];
        foreach ($this->value as $file_id => $file_info) {
            /** @var $file_info FileInfoResult */
            $values[$file_id] = [
                'file_info' => $file_info,
                'additional_inputs' => $this->templates[$file_id]->getValue(),
            ];
        }

        return $values;
    }

    /**
     * @inheritDoc
     */
    public function withInput(InputData $post_input)
    {
        $content = [];
        $clone = clone $this;
        $post_data = $post_input->get($this->getName());

        foreach ($post_data as $file_data) {
            $file_id = $file_data['file_id'];
            if ($this->hasZipExtractOptions()) {
                $content[$file_id]['zip_extract'] = (isset($file_data['zip_extract']) && 'checked' === $file_data['zip_extract']);
                $content[$file_id]['zip_structure'] = (isset($file_data['zip_structure']) && 'checked' === $file_data['zip_structure']);
            }

//            if (null !== ($templates = $this->getNestedInputTemplates())) {
//                foreach ($templates as $key => $template) {
//                    $content[$file_id][$key] = $file_data[$key];
//                }
//            }
        }

        // @TODO: apply trafos of nested inputs here, as they cannot be done
        //        in the instance itself.

        $clone->content = new Ok($content);

        return $clone;
    }

    /**
     * Extend parents method due to implementation of NestedInputs (sub-inputs).
     *
     * @inheritDoc
     */
    public function withDisabled($is_disabled)
    {
        $clone = parent::withDisabled($is_disabled);
        if (null !== $this->template) {
            $clone->template = $this->template->withDisabled($is_disabled);
        }

        return $clone;
    }

    /**
     * Extend parents method due to implementation of NestedInputs (sub-inputs).
     *
     * @inheritDoc
     */
    public function withRequired($is_required)
    {
        $clone = parent::withRequired($is_required);
        if (null !== $this->template) {
            $clone->template = $this->template->withRequired($is_required);
        }

        return $clone;
    }

    /**
     * Extend parents method due to implementation of NestedInputs (sub-inputs).
     *
     * @inheritDoc
     */
    public function withOnUpdate(Signal $signal)
    {
        $clone = parent::withOnUpdate($signal);
        if (null !== $this->template) {
            $clone->template = $this->template->withOnUpdate($signal);
        }

        return $clone;
    }

    /**
     * Extend parents method to name nested sub-inputs as well.
     *
     * @inheritdoc
     */
    public function withNameFrom(NameSource $source)
    {
        $clone = parent::withNameFrom($source);

        if (null !== $this->template) {
            $template_clone = clone $this->template;
            $template_clone = $template_clone->withNameFrom(new SubordinateNameSource($clone->getName()));

            $clone->template = $template_clone;
        }

        return $clone;
    }

    /**
     * @inheritDoc
     */
    protected function isClientSideValueOk($value) : bool
    {
        // @TODO: let consumers pass along FALSE two times if zip options are enabled.

        if (null === $value) {
            return true;
        }

        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $file_id => $template_value) {
            // if this input has a template for additional inputs,
            // the given value must consist of file-id's (string)
            // mapped to a value that matches the current template's
            // display value. If it has no template, the array must
            // only consist of file-id's (string values).
            if (null !== $this->template) {
                if (!is_string($file_id)) {
                    return false;
                }

                if ($this->template instanceof C\Input\Field\Group) {
                    if (!is_array($template_value)) {
                        return false;
                    }

                    $inputs = $this->template->getInputs();
                    foreach ($template_value as $key => $val) {
                        if (!isset($inputs[$key])) {
                            return false;
                        }

                        if (!$inputs[$key]->isClientSideValueOk($val)) {
                            return false;
                        }
                    }
                } elseif (!$this->template->isClientSideValueOk($template_value)) {
                    return false;
                }
            } elseif (!is_string($template_value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getUpdateOnLoadCode() : \Closure
    {
        return static function ($id) {
            return '';
        };
    }

    /**
     * @inheritDoc
     */
    protected function getConstraintForRequirement()
    {
        return $this->refinery->string();
    }

    /**
     * Merges the given template with zip-options and returns them
     * in a group.
     *
     * @param C\Input\Field\Input $template
     * @return C\Input\Field\Group
     */
    private function mergeTemplateAndZipOptions(C\Input\Field\Input $template) : C\Input\Field\Group
    {
        $zip_options = $this->getZipExtractOptions();

        if ($template instanceof C\Input\Field\Group) {
            $inputs = array_merge_recursive($zip_options, $template->getInputs());
        } else {
            $inputs   = $zip_options;
            $inputs[] = $template;
        }

        return $this->factory->group($inputs);
    }

    /**
     * Converts a FileInfoResult to array data.
     *
     * @param FileInfoResult $file
     * @return array
     */
    private function fileInfoToArray(FileInfoResult $file) : array
    {
        // @TODO: probably hardcode array keys.
        return [
            $this->upload_handler->getFileIdentifierParameterName() => $file->getFileIdentifier(),
            'mime' => $file->getMimeType(),
            'name' => $file->getName(),
            'size' => $file->getSize(),
        ];
    }

    /**
     * Returns the zip-extract option inputs.
     *
     * @return C\Input\Field\Input[]
     */
    private function getZipExtractOptions() : array
    {
        return [
            $this->factory->checkbox($this->lang->txt('zip_extract')),
            $this->factory->checkbox($this->lang->txt('zip_structure')),
        ];
    }
}
