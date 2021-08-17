<?php

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Implementation\Component\Input\Factory as InputFactory;

/**
 * Class Wrapper
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */
class Wrapper extends FileDropzone implements \ILIAS\UI\Component\Dropzone\File\Wrapper
{
    /**
     * @var Component[]
     */
    private $components;

    /**
     * @var InputFactory
     */
    private $input_factory;

    /**
     * @var Input[]|null
     */
    private $inputs;

    /**
     * @var string|null
     */
    private $title;

    /**
     * Wrapper constructor.
     *
     * @param InputFactory  $input_factory
     * @param UploadHandler $upload_handler
     * @param string        $post_url
     * @param array         $components
     */
    public function __construct(InputFactory $input_factory, UploadHandler $upload_handler, string $post_url, array $components)
    {
        $this->checkArgListElements('components', $components, Component::class);

        $this->components = $components;
        $this->input_factory = $input_factory;

        parent::__construct($upload_handler, $post_url);
    }

    /**
     * @inheritDoc
     */
    public function getComponents() : array
    {
        return $this->components;
    }

    /**
     * @inheritdoc
     */
    public function withTitle(string $title) : Wrapper
    {
        $this->checkStringArg("title", $title);

        $clone = clone $this;
        $clone->title = $title;

        return $clone;
    }

    /**
     * @inheritdoc
     */
    public function getTitle() : ?string
    {
        return $this->title;
    }

    /**
     * @inheritDoc
     */
    public function withMetadataInputs(array $inputs) : Wrapper
    {
        $this->checkArgListElements('inputs', $inputs, Input::class);

        $clone = clone $this;
        $clone->inputs = $inputs;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getMetadataInputs() : ?array
    {
        return $this->inputs;
    }

    /**
     * Returns the form needed for file-submissions.
     *
     * @return \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    public function getForm() : \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        $dropzone_file_input = $this->input_factory->field()->file($this->getUploadHandler(), $this->getTitle() ?? '');
        $dropzone_file_input = $dropzone_file_input->withZipExtractOptions($this->hasZipExtractOptions());

        if (null !== $this->getMaxFileSize()) {
            $dropzone_file_input = $dropzone_file_input->withMaxFileSize($this->getMaxFileSize());
        }
        if (null !== $this->getMaxFiles()) {
            $dropzone_file_input = $dropzone_file_input->withMaxFiles($this->getMaxFiles());
        }
        if (null !== $this->getAcceptedMimeTypes()) {
            $dropzone_file_input = $dropzone_file_input->withAcceptedMimeTypes($this->getAcceptedMimeTypes());
        }
        if (null !== $this->getMetadataInputs()) {
            $dropzone_file_input = $dropzone_file_input->withNestedInputs($this->getMetadataInputs());
        }

        return $this->input_factory->container()->form()->standard(
            $this->getPostURL(),
            [$dropzone_file_input]
        );
    }
}
