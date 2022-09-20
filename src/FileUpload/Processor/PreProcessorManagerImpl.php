<?php

namespace ILIAS\FileUpload\Processor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use Psr\Http\Message\StreamInterface;

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
 * Class PreProcessorManagerImpl
 *
 * A pool of preprocessors which can be executed for a particular stream.
 * If once of the processors fail while processing a stream, it will get rejected to protect ILIAS.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0
 *
 * @internal
 */
final class PreProcessorManagerImpl implements PreProcessorManager
{
    /**
     * @var PreProcessor[] $processors
     */
    private array $processors = [];

    /**
     * @inheritDoc
     */
    public function with(PreProcessor $processor): void
    {
        $this->processors[] = $processor;
    }


    /**
     * @inheritDoc
     */
    public function process(FileStream $stream, Metadata $metadata): ProcessingStatus
    {
        try {
            $result = null;
            foreach ($this->processors as $processor) {
                $stream->rewind();
                $result = $processor->process($stream, $metadata);
                if ($result->getCode() === ProcessingStatus::REJECTED) {
                    return $result;
                }
            }

            if (is_null($result)) {
                $result = new ProcessingStatus(ProcessingStatus::OK, 'No processors were registered.');
            }

            return $result;
        } catch (\Exception $ex) {
            return new ProcessingStatus(ProcessingStatus::REJECTED, 'Processor failed with exception message "' . $ex->getMessage() . '"');
        }
    }
}
