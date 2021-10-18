<?php

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use Psr\Http\Message\ServerRequestInterface;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\Input\Field\File;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Component\Input\Field\Group;
use ILIAS\UI\Component\Input\Container\Form\Form;
use ILIAS\UI\Component\Input\Field\FormInput;
use ILIAS\UI\Component\Input\Field\AdditionalFormInputsAware;
use ILIAS\UI\Component\Input\Factory;
use ILIAS\UI\Component\Signal;
use ilLanguage;

/**
 * Class FileDropzone
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */
abstract class FileDropzone implements \ILIAS\UI\Component\Dropzone\File\FileDropzone
{
    use JavaScriptBindable;
    use ComponentHelper;
    use Triggerer;

    /**
     * @var string name of the event in javascript, e.g.
     *             used with jQuery .on('drop', ...).
     */
    private const EVENT = 'drop';

    /**
     * @var Factory
     */
    protected Factory $factory;

    /**
     * @var Form
     */
    protected Form $form;

    /**
     * @var UploadHandler
     */
    private UploadHandler $upload_handler;

    /**
     * @var Group
     */
    private Group $zip_options_template;

    /**
     * @var FormInput|null
     */
    private ?FormInput $input_template = null;

    /**
     * @var FormInput[]|null
     */
    private ?array $additional_inputs = null;

    /**
     * @var string
     */
    private string $post_url;

    /**
     * @var string[]|null
     */
    private ?array $accepted_mime_types = null;

    /**
     * @var int|null
     */
    private ?int $max_file_size = null;

    /**
     * @var int|null
     */
    private int $max_files = 1;

    /**
     * @var bool
     */
    private bool $has_zip_options;

    /**
     * FileDropzone Constructor
     *
     * @param Factory       $factory
     * @param ilLanguage    $lang
     * @param UploadHandler $upload_handler
     * @param string        $post_url
     * @param bool          $with_zip_options
     */
    public function __construct(
        Factory $factory,
        ilLanguage $lang,
        UploadHandler $upload_handler,
        string $post_url,
        bool $with_zip_options = false
    ) {
        $this->factory          = $factory;
        $this->post_url         = $post_url;
        $this->upload_handler   = $upload_handler;
        $this->has_zip_options  = $with_zip_options;

        $this->zip_options_template = $factory->field()->group([
            $factory->field()->checkbox($lang->txt('zip_extract')),
            $factory->field()->checkbox($lang->txt('zip_structure')),
        ]);
    }

    //
    // BEGIN FILE IMPLEMENTATION
    //

    /**
     * @inheritDoc
     */
    public function getUploadHandler() : UploadHandler
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
    public function getZipExtractOptionsTemplate() : Group
    {
        return $this->zip_options_template;
    }

    /**
     * @inheritDoc
     */
    public function withAcceptedMimeTypes(array $mime_types) : File
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
    public function withMaxFileSize(int $size_in_bytes) : File
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
    public function withMaxFiles(int $amount) : File
    {
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
    // END FILE IMPLEMENTATION
    //

    //
    // BEGIN ADDITIONAL-INPUTS-AWARE IMPLEMENTATION
    //

    /**
     * @inheritDoc
     */
    public function withTemplateForAdditionalInputs(FormInput $template) : AdditionalFormInputsAware
    {
        $this->checkArgInstanceOf('template', $template, FormInput::class);

        $clone = clone $this;
        $clone->input_template = $template;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getTemplateForAdditionalInputs() : ?FormInput
    {
        return $this->input_template;
    }

    /**
     * @inheritDoc
     */
    public function getAdditionalInputs() : ?array
    {
        return $this->additional_inputs;
    }

    //
    // END ADDITIONAL-INPUTS-AWARE IMPLEMENTATION
    //

    //
    // BEGIN FILE-DROPZONE IMPLEMENTATION
    //

    /**
     * @inheritDoc
     */
    public function getPostURL() : string
    {
        return $this->post_url;
    }

    /**
     * @inheritDoc
     */
    public function withOnDrop(Signal $signal)
    {
        return $this->withTriggeredSignal($signal, self::EVENT);
    }

    /**
     * @inheritDoc
     */
    public function withAdditionalDrop(Signal $signal)
    {
        return $this->appendTriggeredSignal($signal, self::EVENT);
    }

    /**
     * @inheritDoc
     */
    public function withRequest(ServerRequestInterface $request) : \ILIAS\UI\Component\Dropzone\File\FileDropzone
    {
        $clone = clone $this;
        $clone->form = $this->getForm()->withRequest($request);

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return $this->form->getData();
    }

    /**
     * Returns the form needed to submit the dropped/uploaded files.
     *
     * @return \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    abstract public function getForm() : \ILIAS\UI\Component\Input\Container\Form\Standard;

    //
    // END FILE-DROPZONE IMPLEMENTATION
    //
}
