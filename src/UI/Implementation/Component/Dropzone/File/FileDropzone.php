<?php

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Implementation\Component\Triggerer;
use ILIAS\UI\Implementation\Component\JavaScriptBindable;
use ILIAS\UI\Component\Input\Field\File;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class FileDropzone
 *
 * @author Thibeau Fuhrer <thf@studer-raimann.ch>
 *
 * @package ILIAS\UI\Implementation\Component\Dropzone\File
 */
abstract class FileDropzone implements \ILIAS\UI\Component\Dropzone\File\FileDropzone
{
    use Triggerer;
    use ComponentHelper;
    use JavaScriptBindable;

    /**
     * @var string name of the event in javascript, e.g.
     *             used with jQuery .on('drop', ...).
     */
    private const EVENT = 'drop';

    /**
     * @var UploadHandler
     */
    private $upload_handler;

    /**
     * @var string
     */
    private $post_url;

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
     * @var bool
     */
    private $zip_options = false;

    /**
     * @var \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    private $form;

    /**
     * FileDropzone constructor.
     *
     * @param UploadHandler $upload_handler
     * @param string        $post_url
     */
    public function __construct(UploadHandler $upload_handler, string $post_url)
    {
        $this->upload_handler = $upload_handler;
        $this->post_url = $post_url;
    }

    /**
     * Returns the form needed to submit the dropped/uploaded files.
     *
     * @return \ILIAS\UI\Component\Input\Container\Form\Standard
     */
    abstract protected function getForm() : \ILIAS\UI\Component\Input\Container\Form\Standard;

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
    public function getPostURL() : string
    {
        return $this->post_url;
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
        if (null !== $this->max_files && 1 < $this->max_files) {
            return $this->max_files;
        }

        return 1;
    }


    /**
     * @inheritDoc
     */
    public function withZipExtractOptions(bool $with_options) : FileDropzone
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
    public function withOnDrop(\ILIAS\UI\Component\Signal $signal)
    {
        return $this->withTriggeredSignal($signal, self::EVENT);
    }

    /**
     * @inheritDoc
     */
    public function withAdditionalDrop(\ILIAS\UI\Component\Signal $signal)
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
}
