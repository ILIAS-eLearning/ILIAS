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
     * File constructor
     *
     * @param DataFactory                 $data_factory
     * @param \ilLanguage                 $lang
     * @param Factory                     $refinery
     * @param C\Input\Field\UploadHandler $handler
     * @param                             $label
     * @param                             $byline
     */
    public function __construct(
        DataFactory $data_factory,
        \ilLanguage $lang,
        Factory $refinery,
        C\Input\Field\UploadHandler $handler,
        $label,
        $byline
    ) {
        $this->lang = $lang;
        $this->upload_handler = $handler;
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
//        input array option:
//
//        $this->checkArgList('inputs', $inputs, Input::class);
//        $clone = clone $this;
//        $clone->templates = $inputs;
//        return $clone;

        $this->checkArgInstanceOf('input', $input, Input::class);

        $clone = clone $this;
        $clone->template = $input;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getTemplateForAdditionalInputs() : ?C\Input\Field\Input
    {
//        input array option:
//
//        return $this->templates;

        return $this->template;
    }

    /**
     * @inheritDoc
     */
    public function getPreparedTemplatesForAdditionalInputs() : ?array
    {
//        input array option:
//
//        return $this->prepared_templates;

        return $this->templates;
    }

    /**
     * @inheritDoc
     */
    public function withValue($value)
    {
        $this->checkArg("value", $this->isClientSideValueOk($value), "Display value does not match input type.");

        $clone = clone $this;

        foreach ($value as $file_id => $template_value) {
            if (null !== $this->template) {
                $clone->templates[] = $this->template->withValue($template_value);
                $clone->value[] = $file_id;
            } else {
                $clone->value[] = $template_value;
            }
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
        foreach ($this->value as $index => $file_id) {
            $values[$file_id] = $this->templates[$index]->getValue();
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
    public function getSubordinateNameSource() : NameSource
    {
        return $this->name_source;
    }

    /**
     * @inheritDoc
     */
    protected function isClientSideValueOk($value) : bool
    {
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
                if (!is_string($file_id) || !$this->template->isClientSideValueOk($template_value)) {
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
}
