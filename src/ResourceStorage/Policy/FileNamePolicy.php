<?php

namespace ILIAS\ResourceStorage\Policy;

/**
 * Interface FileNamePolicy
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface FileNamePolicy
{
    /**
     * @param string $extension
     * @return bool
     * @throws FileNamePolicyException
     */
    public function check(string $extension) : bool;

    public function isValidExtension(string $extension) : bool;

    public function isBlockedExtension(string $extension) : bool;

    public function prepareFileNameForConsumer(string $filename_with_extension) : string;
}
