<?php

namespace ILIAS\FileUpload\Processor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use Psr\Http\Message\StreamInterface;

/**
 * Class PreProcessorManager
 *
 * The pre processor manager is used to create pools of processors and invoke them for a particular
 * stream.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since   5.3
 * @version 1.0
 *
 * @public
 */
interface PreProcessorManager
{

    /**
     * Adds the processor to the current manager.
     * It it possible to add an arbitrary number of processors.
     *
     * @param PreProcessor $processor The processor which should be added.
     *
     * @return void
     * @since 5.3
     */
    public function with(PreProcessor $processor);


    /**
     * Invokes the registered processors until one rejects the file or fails.
     * The file must be discarded if this method returns a rejected status.
     *
     * @param FileStream                 $stream   The stream of the current file.
     * @param Metadata                   $metadata The metadata of the current file.
     *
     * @return ProcessingStatus
     * @since 5.3
     */
    public function process(FileStream $stream, Metadata $metadata);
}
