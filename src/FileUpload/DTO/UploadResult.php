<?php

namespace ILIAS\FileUpload\DTO;

use ILIAS\FileUpload\Collection\ImmutableStringMap;
use ILIAS\FileUpload\ScalarTypeCheckAware;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class UploadResult
 *
 * The upload results are used to tell ILIAS about the file uploads.
 * This class only purpose is to transport data.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 *
 * @public
 */
final class UploadResult
{
    use ScalarTypeCheckAware;
    private string $name;
    private int $size;
    private string $mimeType;
    private ImmutableStringMap $metaData;
    private ProcessingStatus $status;
    private string $path;


    /**
     * UploadResult constructor.
     *
     * @param string             $name     The name of the uploaded file.
     * @param int                $size     The original file size.
     * @param string             $mimeType The mime type of the uploaded file.
     * @param ImmutableStringMap $metaData Additional meta data. Make sure to wrap the instance
     *                                     with an ImmutableMapWrapper if the instance is mutable.
     * @param ProcessingStatus   $status   The status code either OK or REJECTED.
     * @param string             $path     The path to the newly moved file.
     *
     * @since 5.3
     */
    public function __construct(string $name, int $size, string $mimeType, ImmutableStringMap $metaData, ProcessingStatus $status, string $path)
    {
        $this->stringTypeCheck($name, "name");
        $this->stringTypeCheck($mimeType, "mimeType");
        $this->stringTypeCheck($path, "path");
        $this->intTypeCheck($size, "size");

        $this->name = $name;
        $this->size = $size;
        $this->mimeType = $mimeType;
        $this->metaData = $metaData;
        $this->status = $status;
        $this->path = $path;
    }


    /**
     * @since 5.3
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * @since 5.3
     */
    public function getSize(): int
    {
        return $this->size;
    }


    /**
     * @since 5.3
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }


    /**
     * @since 5.3
     */
    public function getMetaData(): ImmutableStringMap
    {
        return $this->metaData;
    }


    /**
     * @since 5.3
     */
    public function getStatus(): ProcessingStatus
    {
        return $this->status;
    }


    public function isOK(): bool
    {
        return $this->status->getCode() === ProcessingStatus::OK;
    }


    /**
     * @since 5.3
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
