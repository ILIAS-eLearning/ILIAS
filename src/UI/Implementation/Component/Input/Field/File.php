<?php

namespace ILIAS\UI\Implementation\Component\Input\Field;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Refinery\Factory;
use ILIAS\UI\Component as C;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Component\Input\Field\NestedInput;
use ILIAS\UI\Component\Signal;
use ILIAS\UI\Implementation\Component\Input\InputData;
use ILIAS\UI\Implementation\Component\Input\NameSource;

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
     * @var Input[]
     */
    private $input_templates;

    /**
     * @var Input[]
     */
    private $inputs;

    /**
     * File constructor
     *
     * @param DataFactory                 $data_factory
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
    public function withNestedInputs(array $inputs) : C\Input\Field\NestedInput
    {
        $this->checkArgListElements('inputs', $inputs, Input::class);

        $clone = clone $this;
        $clone->input_templates = $inputs;

        return $clone;
    }

    /**
     * Returns the nested input templates of this input.
     *
     * Note that this method is only needed during the render process.
     *
     * @return Input[]|null
     */
    public function getNestedInputTemplates() : ?array
    {
        return $this->input_templates;
    }

    /**
     * @inheritDoc
     */
    public function getNestedInputs() : ?array
    {
        return $this->inputs;
    }

    /**
     * @inheritDoc
     */
    public function withValue($value)
    {
        $this->checkArg("value", $this->isClientSideValueOk($value), "Display value does not match input type.");
        $clone = clone $this;

        foreach ($value as $file_id => $input_values) {
            $clone->value[] = $file_id;
            foreach ($this->input_templates as $key => $template) {
                $array_key = "{$file_id}_$key";
                $clone->inputs[$array_key] = $template->withValue($input_values[$key]);
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
        foreach ($this->value as $file_id) {
            foreach ($this->input_templates as $key => $template) {
                $array_key = "{$file_id}_$key";
                $values[$file_id][$key] = $this->inputs[$array_key]->getValue();
            }
        }

        return $values;
    }

    /**
     * @inheritDoc
     */
    public function withNestedInput(InputData $post_input) : NestedInput
    {
        if (0 === count($this->getNestedInputs())) return $this;

        $clone = clone $this;

        $inputs = [];
        $contents = [];
        $error = false;

        foreach ($this->getNestedInputs() as $key => $input) {
            $inputs[$key] = $input->withInput($post_input);
            $content = $inputs[$key]->getContent();

            // null pointers can be ignored I guess.
            if ($content->isError()) {
                $error = true;
            } else {
                $contents[$key] = $content->value();
            }
        }

        $clone->inputs = $inputs;

        if ($error) {
            $clone->content = $clone->data_factory->error($this->lang->txt("ui_error_in_group"));
        } else {
            $clone->content = $clone->applyOperationsTo($contents);
        }

        if ($clone->content->isError()) {
            $clone = $clone->withError("" . $clone->content->error());
        }

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
        $clone->inputs = array_map(static function ($input) use ($is_disabled) {
            return $input->withDisabled($is_disabled);
        }, $this->inputs);

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
        $clone->inputs = array_map(static function ($input) use ($is_required) {
            return $input->withRequired($is_required);
        }, $this->inputs);

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
        $clone->inputs = array_map(static function ($input) use ($signal) {
            return $input->withOnUpdate($signal);
        }, $this->inputs);

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

        $named_inputs = [];
        foreach ($this->inputs as $key => $input) {
            $named_inputs[$key] = $input->withNameFrom($source);
        }

        $clone->inputs = $named_inputs;

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
        foreach ($value as $string => $array) {
            if (!is_string($string) ||
                !is_array($array) ||
                count($this->input_templates) !== count($array)
            ) {
                return false;
            }

            foreach ($array as $key => $input_value) {
                if (!array_key_exists($key, $this->input_templates)) {
                    return false;
                }
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
