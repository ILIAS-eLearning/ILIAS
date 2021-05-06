<?php

namespace ILIAS\ResourceStorage\Policy;

/**
 * Class NoneFileNamePolicy
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class NoneFileNamePolicy implements FileNamePolicy
{
    public function check(string $extension) : bool
    {
        return true;
    }

    public function isValidExtension(string $extension) : bool
    {
        return true;
    }

    public function isBlockedExtension(string $extension) : bool
    {
        return true;
    }

    public function prepareFileNameForConsumer(string $filename_with_extension) : string
    {
        return $filename_with_extension;
    }

}
