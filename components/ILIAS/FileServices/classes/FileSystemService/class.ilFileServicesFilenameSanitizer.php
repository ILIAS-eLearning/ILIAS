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

use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizerImpl;

/**
 * Class ilFileServicesFilenameSanitizer
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilFileServicesFilenameSanitizer extends FilenameSanitizerImpl
{
    public function __construct(ilFileServicesSettings $settings)
    {
        $whitelisted = array_diff($settings->getWhiteListedSuffixes(), $settings->getBlackListedSuffixes());

        // the negative list does not apply to users holding the bypass permission, the
        // same rule ilFileServicesPolicy::isBlockedExtension() follows. Only suffixes the
        // negative list removed from the default or the positive list come back; the
        // prohibited suffixes and the hard coded protection against php suffixes stay
        // in effect, see https://mantis.ilias.de/view.php?id=47828
        if ($settings->isByPassAllowedForCurrentUser()) {
            $removed_by_negative_list = array_intersect(
                $settings->getWhiteListNegative(),
                array_merge($settings->getDefaultWhitelist(), $settings->getWhiteListPositive())
            );
            $whitelisted = array_merge(
                $whitelisted,
                array_diff($removed_by_negative_list, $settings->getProhibited())
            );
        }

        parent::__construct($whitelisted);
    }
}
