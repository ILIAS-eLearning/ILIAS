<?php

namespace ILIAS\FileUpload\DTO;

use ILIAS\FileUpload\Collection\EntryLockingStringMap;
use ILIAS\FileUpload\Collection\StringMap;
use ILIAS\FileUpload\ScalarTypeCheckAware;

/**
 * Class Metadata
 *
 * The meta data class holds all the data which are passed to each processor.
 * This class only purpose is to transport data.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 *
 * @public
 */
final class Metadata
{
    use ScalarTypeCheckAware;
    private string $filename;
    private int $uploadSize;
    private string $mimeType;
    private StringMap $additionalMetaData;


    /**
     * Metadata constructor.
     *
     * @param string $filename The filename of the uploaded file.
     * @param int    $size     The original size of the uploaded file.
     * @param string $mimeType The mime type of the uploaded file.
     *
     * @throws \InvalidArgumentException Thrown if the arguments are not matching with the expected
     *                                   types.
     * @since 5.3
     */
    public function __construct(string $filename, int $size, string $mimeType)
    {
        $this->stringTypeCheck($filename, "filename");
        $this->intTypeCheck($size, "size");
        $this->stringTypeCheck($mimeType, "mimeType");

        $this->filename = $filename;
        $this->uploadSize = $size;
        $this->mimeType = $mimeType;
        $this->additionalMetaData = new EntryLockingStringMap();
    }


    /**
     * The filename supplied by the browser.
     * Please be aware of the fact that this value can be potentially unsafe.
     *
     * @since 5.3
     */
    public function getFilename(): string
    {
        return $this->filename;
    }


    /**
     * Overwrite the current filename.
     *
     * @param string $filename The new filename.
     *
     * @since 5.3
     */
    public function setFilename(string $filename): self
    {
        $this->stringTypeCheck($filename, "filename");

        $this->filename = $filename;

        return $this;
    }


    /**
     * This is always the original file size which was determined by the http service.
     * The current size is provided by the size method of the Stream passed to the processor.
     * Please use the filesystem service to get the file size outside of the processors.
     *
     * @since 5.3
     */
    public function getUploadSize(): int
    {
        return $this->uploadSize;
    }


    /**
     * Client supplied mime type of the uploaded. This
     * value must be threaded as unreliable.
     *
     * @since 5.3
     */
    public function getMimeType(): string
    {
        return $this->mimeType;
    }


    /**
     * Overwrite the current mime type of the file.
     *
     * @param string $mimeType The new mime type if the file.
     *
     * @since 5.3
     */
    public function setMimeType(string $mimeType): self
    {
        $this->stringTypeCheck($mimeType, "mimeType");

        $this->mimeType = $mimeType;

        return $this;
    }


    /**
     * Provides a string map implementation which allows the processors to store additional values.
     * The string map implementation used by the meta data refuses to overwrite values.
     *
     * @since 5.3
     */
    public function additionalMetaData(): StringMap
    {
        return $this->additionalMetaData;
    }
}
