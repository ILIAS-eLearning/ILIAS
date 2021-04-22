<?php

namespace ILIAS\ResourceStorage\Policy;

/**
 * Class FileNamePolicyStack
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
class FileNamePolicyStack implements FileNamePolicy
{
    /**
     * @var FileNamePolicy[]
     */
    protected $stack = [];

    public function addPolicy(FileNamePolicy $policy) : void
    {
        $this->stack[] = $policy;
    }

    public function isValidExtension(string $extension) : bool
    {
        foreach ($this->stack as $policy) {
            if (!$policy->isValidExtension($extension)) {
                return false;
            }
        }
        return true;
    }

    public function isBlockedExtension(string $extension) : bool
    {
        foreach ($this->stack as $policy) {
            if (!$policy->isBlockedExtension($extension)) {
                return false;
            }
        }
        return true;
    }

    public function prepareFileNameForConsumer(string $filename_with_extension) : string
    {
        foreach ($this->stack as $policy) {
            $filename_with_extension = $policy->prepareFileNameForConsumer($filename_with_extension);
        }
        return $filename_with_extension;
    }

    public function check(string $extension) : bool
    {
        foreach ($this->stack as $policy) {
            $policy->check($extension);
        }
        return true;
    }

}
