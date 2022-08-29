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
 *********************************************************************/

namespace ILIAS\ResourceStorage\Policy;

/**
 * Class WhiteAndBlacklistedFileNamePolicy
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
abstract class WhiteAndBlacklistedFileNamePolicy implements FileNamePolicy
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

    public function isValidExtension(string $extension): bool
    {
        return in_array($extension, $this->whitelisted) && !in_array($extension, $this->blacklisted);
    }

    public function isBlockedExtension(string $extension): bool
    {
        return in_array($extension, $this->blacklisted);
    }

    public function check(string $extension): bool
    {
        if ($this->isBlockedExtension($extension)) {
            throw new FileNamePolicyException("Extension '$extension' is blacklisted.");
        }
        return true;
    }
}
