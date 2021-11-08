<?php declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Dropzone;

use ILIAS\UI\Component\Dropzone as D;

/**
 * Class Factory
 *
 * Default implementation for File Dropzone factory.
 *
 * @author  nmaerchy <nm@studer-raimann.ch>
 *
 * @package UI\Implementation\Component\Dropzone
 */
class Factory implements D\Factory
{
    protected File\Factory $file_factory;

    public function __construct(File\Factory $file_factory)
    {
        $this->file_factory = $file_factory;
    }

    /**
     * @inheritDoc
     */
    public function file() : D\File\Factory
    {
        return $this->file_factory;
    }
}
