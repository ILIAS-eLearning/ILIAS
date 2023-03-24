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
        global $DIC;
        $as_ascii = (bool) !$DIC->clientIni()->readVariable(
            'file_access',
            'disable_ascii'
        );
        $valid_filename = \ilFileUtils::getValidFilename($filename_with_extension);

        // remove all control characters, see https://mantis.ilias.de/view.php?id=34975
        $valid_filename = preg_replace('/&#.*;/U', '_', $valid_filename, 1);

        if ($as_ascii) {
            return \ilUtil::getASCIIFilename($valid_filename);
        }
        return $valid_filename;
    }

    public function check(string $extension) : bool
    {
        if ($this->isBlockedExtension($extension)) {
            throw new FileNamePolicyException("Extension '$extension' is blacklisted.");
        }
        return true;
    }
}
