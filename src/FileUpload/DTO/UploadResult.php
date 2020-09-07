<?php

namespace ILIAS\FileUpload\DTO;

use ILIAS\FileUpload\Collection\ImmutableStringMap;
use ILIAS\FileUpload\ScalarTypeCheckAware;

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
    /**
     * @var string $name
     */
    private $name;
    /**
     * @var int $size
     */
    private $size;
    /**
     * @var string $mimeType
     */
    private $mimeType;
    /**
     * @var ImmutableStringMap $metaData
     */
    private $metaData;
    /**
     * @var ProcessingStatus $status
     */
    private $status;
    /**
     * @var string $path
     */
    private $path;


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
    public function __construct($name, $size, $mimeType, ImmutableStringMap $metaData, ProcessingStatus $status, $path)
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
     * @return string
     * @since 5.3
     */
    public function getName()
    {
        return $this->name;
    }


    /**
     * @return int
     * @since 5.3
     */
    public function getSize()
    {
        return $this->size;
    }


    /**
     * @return string
     * @since 5.3
     */
    public function getMimeType()
    {
        return $this->mimeType;
    }


    /**
     * @return ImmutableStringMap
     * @since 5.3
     */
    public function getMetaData()
    {
        return $this->metaData;
    }


    /**
     * @return ProcessingStatus
     * @since 5.3
     */
    public function getStatus()
    {
        return $this->status;
    }


    /**
     * @return string
     * @since 5.3
     */
    public function getPath()
    {
        return $this->path;
    }
}
