<?php
/**
 * Class Factory
 *
 * Default implementation for File Dropzone factory.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package UI\Implementation\Component\Dropzone
 */

namespace ILIAS\UI\Implementation\Component\Dropzone;

class Factory implements \ILIAS\UI\Component\Dropzone\Factory
{
    /**
     * @var File\Factory
     */
    protected $file_factory;

    public function __construct(File\Factory $file_factory)
    {
        $this->file_factory = $file_factory;
    }

    /**
     * @inheritDoc
     */
    public function file()
    {
        return $this->file_factory;
    }
}
