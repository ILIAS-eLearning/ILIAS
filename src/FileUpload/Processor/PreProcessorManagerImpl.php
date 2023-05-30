<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

namespace ILIAS\FileUpload\Processor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use Psr\Http\Message\StreamInterface;

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
    private $processors = [];

    /**
     * @inheritDoc
     */
    public function with(PreProcessor $processor)
    {
        $this->processors[] = $processor;
    }


    /**
     * @inheritDoc
     */
    public function process(FileStream $stream, Metadata $metadata)
    {
        try {
            $result = null;
            foreach ($this->processors as $processor) {
                $stream->rewind();
                $result = $processor->process($stream, $metadata);
                if ($result->getCode() === ProcessingStatus::REJECTED || $result->getCode() === ProcessingStatus::DENIED) {
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
