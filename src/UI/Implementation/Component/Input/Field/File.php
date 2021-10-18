<?php

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\UI\Implementation\Component\Input\NameSource;
use ILIAS\UI\Implementation\Component\Input\Container\Form\ArrayInputData;
use ILIAS\UI\Implementation\Component\Input\SubordinateNameSource;
use ILIAS\UI\Implementation\Component\Input\Field\Factory as InputFactory;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Component\Input\Field\FormInput;
use ILIAS\FileUpload\Handler\FileInfoResult;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Data\Factory as DataFactory;
use ILIAS\UI\Component as C;
use ILIAS\Data\Result\Ok;
use ilLanguage;
use _HumbugBox0b2f2d5c77b8\Symfony\Component\Console\Exception\LogicException;

/**
 * Class File is responsible for managing file inputs.
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Input\Field
 */
class File extends AdditionalFormInputAwareInput implements C\Input\Field\File
{
    /**
     * @var string array key used for storing the retrieved file-
     *             info result in $this->values.
     */
    public const FILE_INFO_RESULT_KEY = 'file_info';

    /**
     * @var InputFactory
     */
    private InputFactory $factory;

    /**
     * @var UploadHandler
     */
    private UploadHandler $upload_handler;

    /**
     * @var C\Input\Field\Group
     */
    private C\Input\Field\Group $zip_options_template;

    /**
     * @var string[]|null
     */
    private ?array $accepted_mime_types = null;

    /**
     * @var int|null
     */
    private ?int $max_file_size = null;

    /**
     * @var int
     */
    private int $max_files = 1;

    /**
     * @var bool
     */
    private bool $has_zip_options;

    /**
     * File Constructor
     *
     * @param InputFactory  $factory
     * @param DataFactory   $data_factory
     * @param ilLanguage    $lang
     * @param Refinery      $refinery
     * @param UploadHandler $upload_handler
     * @param string        $label
     * @param string|null   $byline
     * @param bool          $with_zip_options
     */
    public function __construct(
        InputFactory $factory,
        DataFactory $data_factory,
        ilLanguage $lang,
        Refinery $refinery,
        UploadHandler $upload_handler,
        string $label,
        string $byline = null,
        bool $with_zip_options = false
    ) {
        $this->factory          = $factory;
        $this->upload_handler   = $upload_handler;
        $this->has_zip_options  = $with_zip_options;

        $this->zip_options_template = $this->factory->group([
            $this->factory->checkbox($lang->txt('zip_extract')),
            $this->factory->checkbox($lang->txt('zip_structure')),
        ]);

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
    public function hasZipExtractOptions() : bool
    {
        return $this->has_zip_options;
    }

    /**
     * @inheritDoc
     */
    public function getZipExtractOptionsTemplate() : C\Input\Field\Group
    {
        return $this->zip_options_template;
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
        $this->checkIntArg('size_in_bytes', $size_in_bytes);
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
        return $this->max_files;
    }

    //
    // BEGIN OVERWRITTEN METHODS
    //

    /**
     * Parent method is overwritten in order to merge templates with the
     * zip-extract options of this input.
     *
     * @inheritDoc
     */
    public function withTemplateForAdditionalInputs(FormInput $template) : self
    {
        /** @var $clone self */
        $clone =  parent::withTemplateForAdditionalInputs($template);

        if ($clone->hasZipExtractOptions()) {
            $clone->input_template = $this->mergeInputWithZipOptions($clone->input_template);
        }

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withValue($value)
    {
        $this->checkArg("value", $this->isClientSideValueOk($value), "Display value does not match input type.");
        $clone = clone $this;

        foreach ($value as $file_id => $template_value) {
            if (null !== ($template = $clone->getTemplateForAdditionalInputs()) || $clone->hasZipExtractOptions()) {
                $file_info = $clone->upload_handler->getInfoResult($file_id);
                if (null === $file_info) {
                    throw new LogicException("Provided file for resource id '$file_id' not found.");
                }

                if (null !== $template) {
                    $template = $clone->input_template->withValue($template_value);
                    if ($clone->has_zip_options && 'application/zip' === $file_info->getMimeType()) {
                        $template = $clone->mergeInputWithZipOptions($template);
                    }

                    $clone->additional_inputs[$file_id] = $template;
                } else {
                    $zip_options = $this->getZipExtractOptionsTemplate();
                    $zip_options = $zip_options->withValue($template_value);

                    $clone->additional_inputs[$file_id] = $zip_options;
                }

                $clone->value[$file_id] = $file_info;
            } else {
                $file_info = $clone->upload_handler->getInfoResult($template_value);
                if (null === $file_info) {
                    throw new LogicException("Provided file for resource id '$template_value' not found.");
                }

                $clone->value[$template_value] = $file_info;
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
        /** @var $file_info FileInfoResult */
        foreach ($this->value as $file_id => $file_info) {
            if (!empty($this->additional_inputs)) {
                $template_value = $this->additional_inputs[$file_id]->getValue();
                if (is_array($template_value) && !empty($template_value)) {
                    foreach ($template_value as $key => $value) {
                        $values[$file_id][$key] = $value;
                    }
                } else {
                    $values[$file_id][] = $template_value;
                }

                // file-info is appended as last array entry, in
                // order to "consistently" fetch it with PHPs
                // array_key_last() function.
                $values[$file_id][] = $file_info;
            } else {
                $values[$file_id] = $file_info;
            }
        }

        return $values;
    }

    /**
     * @TODO: discuss how transformations are applied/handled.
     *
     * @inheritDoc
     */
    public function withInput(InputData $post_input)
    {
        if (null === $this->getName()) {
            throw new \LogicException("Can only collect if input has a name.");
        }

        $clone           = clone $this;
        $post_data       = $post_input->getOr($this->getName(), null);
        $file_identifier = $clone->upload_handler->getFileIdentifierParameterName();

        if (empty($post_data)) {
            $clone->content = new Ok($post_data);
            return $clone;
        }

        $contents = [];
        foreach ($post_data as $file_data) {
            $file_id = $file_data[$file_identifier];

            if (null !== ($template = $clone->getTemplateForAdditionalInputs()) || $clone->hasZipExtractOptions()) {
                $data = [];
                foreach ($file_data as $key => $value) {
                    if ($key !== $file_identifier) {
                        $input_name = "{$clone->getName()}[" . SubordinateNameSource::INDEX_PLACEHOLDER . "][$key]";
                        $data[$input_name] = $value;
                    }
                }

                $template = ($template) ?: $clone->getZipExtractOptionsTemplate();
                $template = $template->withInput(new ArrayInputData($data));
                $content  = $template->getContent();

                if ($content->isOk()) {
                    $content = $content->value();
                    if (is_array($content) && !empty($content)) {
                        foreach ($content as $key => $value) {
                            $contents[$file_id][$key] = $value;
                        }
                    } else {
                        $contents[$file_id] = $content;
                    }
                }
            } else {
                $contents[] = $file_id;
            }
        }

        $clone->content = $clone->applyOperationsTo($contents);

        if ($clone->content->isError()) {
            $clone = $clone->withError($clone->content->error());
        }

        return $clone;
    }

    /**
     * Parent method is overwritten in order to generate a subordinate
     * name for the zip options if they weren't added to the template (
     * because withTemplateForAdditionalInputs was never called).
     *
     * @inheritDoc
     */
    public function withNameFrom(NameSource $source)
    {
        /** @var $clone self */
        $clone = parent::withNameFrom($source);

        if ($clone->hasZipExtractOptions() && null === $clone->getTemplateForAdditionalInputs()) {
            $clone->zip_options_template = $clone->zip_options_template->withNameFrom(
                new SubordinateNameSource($clone->getName())
            );
        }

        return $clone;
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
            if (null !== ($template = $this->getTemplateForAdditionalInputs())) {
                if (!is_string($file_id)) {
                    return false;
                }

                if (!$template->isClientSideValueOk($template_value)) {
                    return false;
                }
            } elseif ($this->hasZipExtractOptions()) {
                return true;
            } elseif (!is_string($template_value)) {
                return false;
            }
        }

        return true;
    }

    //
    // END OVERWRITTEN METHODS
    //

    /**
     * @inheritDoc
     */
    public function getUpdateOnLoadCode() : \Closure
    {
        return static function () {};
    }

    /**
     * @inheritDoc
     */
    protected function getConstraintForRequirement()
    {
        return static function () {};
    }

    /**
     * Merges the given input with the zip-extract options by putting
     * them into a group input.
     *
     * @param C\Input\Field\Input $input
     * @return C\Input\Field\Group
     */
    private function mergeInputWithZipOptions(C\Input\Field\Input $input) : C\Input\Field\Group
    {
        $zip_options = $this->getZipExtractOptionsTemplate()->getInputs();
        if ($input instanceof C\Input\Field\Group) {
            $inputs = array_merge_recursive($zip_options, $input->getInputs());
        } else {
            $inputs[] = $zip_options;
            $inputs[] = $input;
        }

        return $this->factory->group($inputs);
    }
}
