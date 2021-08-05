<?php

namespace ILIAS\UI\Implementation\Component\Dropzone\File;

use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Component\ComponentHelper;
use ILIAS\UI\Component\Input\Field\UploadHandler;
use ILIAS\UI\Component\Input\Field\Input;
use ILIAS\UI\Component\Input\Field\File;

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
     * @param UploadHandler $upload_handler
     * @param array         $components
     */
    public function __construct(UploadHandler $upload_handler, array $components)
    {
        $this->checkArgListElements('components', $components, Component::class);

        $this->components = $components;

        parent::__construct($upload_handler);
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
}
