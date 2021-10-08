<?php

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Implementation\Component\Input\Factory as InputFactory;
use ILIAS\UI\Component\Input\Factory;
use ilLanguage;

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
    private array $components;

    /**
     * @var string|null
     */
    private ?string $title;

    /**
     * Wrapper Constructor
     * @param Factory       $factory
     * @param ilLanguage    $lang
     * @param UploadHandler $upload_handler
     * @param string        $post_url
     * @param Component[]   $components
     * @param bool          $with_zip_options
     */
    public function __construct(
        Factory $factory,
        ilLanguage $lang,
        UploadHandler $upload_handler,
        string $post_url,
        array $components,
        bool $with_zip_options = false
    ) {
        $this->components = $components;

        parent::__construct($factory, $lang, $upload_handler, $post_url, $with_zip_options);
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
     * Returns the form needed for file-submissions.
     *
     * @return \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    public function getForm() : \ILIAS\UI\Component\Input\Container\Form\Standard
    {
        $dropzone_file_input = $this->factory->field()->file($this->getUploadHandler(), $this->getTitle() ?? '', $this->hasZipExtractOptions());

        if (null !== ($max_size = $this->getMaxFileSize())) {
            $dropzone_file_input = $dropzone_file_input->withMaxFileSize($max_size);
        }
        if (null !== ($amount = $this->getMaxFiles())) {
            $dropzone_file_input = $dropzone_file_input->withMaxFiles($amount);
        }
        if (null !== ($types = $this->getAcceptedMimeTypes())) {
            $dropzone_file_input = $dropzone_file_input->withAcceptedMimeTypes($types);
        }
        if (null !== ($template = $this->getTemplateForAdditionalInputs())) {
            $dropzone_file_input = $dropzone_file_input->withTemplateForAdditionalInputs($template);
        }

        return $this->factory->container()->form()->standard(
            $this->getPostURL(),
            [
                $dropzone_file_input
            ]
        );
    }
}
