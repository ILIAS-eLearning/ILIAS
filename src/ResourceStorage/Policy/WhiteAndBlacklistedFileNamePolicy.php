<?php

namespace ILIAS\ResourceStorage\Policy;

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
 * Class WhiteAndBlacklistedFileNamePolicy
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * TODO: refactor to make the renaming internal, it uses ilFileUtil currrently
 */
class WhiteAndBlacklistedFileNamePolicy implements FileNamePolicy
{
    /**
     * @var string[]
     */
    protected array $blacklisted = [];
    /**
     * @var string[]
     */
    protected array $whitelisted = [];

    /**
     * WhiteAndBlacklistedFileNamePolicy constructor.
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
