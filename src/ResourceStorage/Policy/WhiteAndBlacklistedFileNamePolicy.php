<?php

namespace ILIAS\ResourceStorage\Policy;

/**
 * Class WhiteAndBlacklistedFileNamePolicy
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * TODO: refactor to make the renaming internal, it uses ilFileUtil currrently
 */
class WhiteAndBlacklistedFileNamePolicy implements FileNamePolicy
{
    protected $blacklisted = [];
    protected $whitelisted = [];

    /**
     * WhiteAndBlacklistedFileNamePolicy constructor.
     * @param array $blacklisted
     * @param array $whitelisted
     */
    public function __construct(array $blacklisted = [], array $whitelisted = [])
    {
        $this->blacklisted = $blacklisted;
        $this->whitelisted = $whitelisted;
    }

    public function isValidExtension(string $extension) : bool
    {
        return \ilFileUtils::hasValidExtension('file.' . $extension);
    }

    public function isBlockedExtension(string $extension) : bool
    {
        $haystack = \ilFileUtils::getExplicitlyBlockedFiles();
        return in_array($extension, $haystack, true);
    }

    public function prepareFileNameForConsumer(string $filename_with_extension) : string
    {
        return \ilFileUtils::getValidFilename($filename_with_extension);
    }

    public function check(string $extension) : bool
    {
        if ($this->isBlockedExtension($extension)) {
            throw new FileNamePolicyException("Extension '$extension' is blacklisted.");
        }
        return true;
    }

}
