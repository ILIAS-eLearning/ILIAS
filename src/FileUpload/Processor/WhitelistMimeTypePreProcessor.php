<?php

namespace ILIAS\FileUpload\Processor;

use ILIAS\Filesystem\Stream\FileStream;
use ILIAS\FileUpload\DTO\Metadata;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use Psr\Http\Message\StreamInterface;

/**
 * Class BlacklistMimeTypePreProcessor
 *
 * Blacklist processor.
 * The processor drops all files which have one of the listed mime types.
 *
 * @author  Nicolas Schäfli <ns@studer-raimann.ch>
 * @since 5.3
 * @version 1.0.0
 *
 * @public
 */
final class WhitelistMimeTypePreProcessor implements PreProcessor
{
    private $whitelist;


    /**
     * WhitelistMimeTypePreProcessor constructor.
     *
     * Whitelist example:
     * ['audio/aiff', 'application/javascript']
     *
     * @param string[] $whitelist The list of mime types which should be allowed.
     *
     * @throws \InvalidArgumentException    Thrown if the supplied whitelist is empty.
     */
    public function __construct(array $whitelist)
    {
        if (count($whitelist) === 0) {
            throw new \InvalidArgumentException('Whitelist must not be empty.');
        }
        
        $this->validateListEntries($whitelist);



        $this->whitelist = $whitelist;
    }


    /**
     * @inheritDoc
     */
    public function process(FileStream $stream, Metadata $metadata)
    {
        if ($this->isWhitelisted($metadata->getMimeType())) {
            return new ProcessingStatus(ProcessingStatus::OK, 'Entity comply with mime type whitelist.');
        }

        return new ProcessingStatus(ProcessingStatus::REJECTED, 'The mime type ' . $metadata->getMimeType() . ' is not whitelisted.');
    }


    /**
     * Checks if the supplied mime type is whitelisted.
     *
     * @param string $mimeType      The mime type which should be checked.
     *
     * @return bool                 True if the mime type is whitelisted otherwise false.
     */
    private function isWhitelisted($mimeType)
    {
        foreach ($this->whitelist as $entry) {
            $entryJunks = explode('/', $entry);
            $mimeTypeJunks = explode('/', $mimeType);

            if (strcmp($entryJunks[0], $mimeTypeJunks[0]) === 0 || strcmp($entryJunks[0], '*') === 0) {
                if (strcmp($entryJunks[1], $mimeTypeJunks[1]) === 0 || strcmp($entryJunks[1], '*') === 0) {
                    return true;
                }
            }
        }
        return false;
    }


    /**
     * Checks if the supplied list contains invalid filters.
     * This method takes no further actions if the supplied list is valid.
     *
     * @param string[] $list    The list which should be validated.
     *
     * @return void
     *
     * @throws \InvalidArgumentException Thrown if the list contains invalid list items.
     */
    private function validateListEntries($list)
    {
        if (in_array('*/*', $list, true)) {
            throw new \InvalidArgumentException('The mime type */* matches all mime types which renders the whole whitelist useless.');
        }
    }
}
