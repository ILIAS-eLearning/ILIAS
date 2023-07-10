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

declare(strict_types=1);

namespace ILIAS\File\Icon;

/**
 * @author Lukas Zehnder <lukas@sr.solutions>
 */
abstract class IconAbstractRepository implements IconRepositoryInterface
{
    private static \ILIAS\Refinery\Factory $refinery;

    public function __construct()
    {
        global $DIC;
        self::$refinery = $DIC->refinery();
    }

    final public function turnSuffixesArrayIntoString(array $a_suffixes): string
    {
        return implode(", ", $a_suffixes);
    }

    /**
     * @return string[]
     */
    final public function turnSuffixesStringIntoArray(string $a_suffixes): array
    {
        $a_suffixes = preg_replace('/\s+/', '', $a_suffixes);
        return explode(",", $a_suffixes);
    }

    final public function hasSuffixInputOnlyAllowedCharacters(array $a_suffixes): bool
    {
        $suffixes_string = $this->turnSuffixesArrayIntoString($a_suffixes);
        $matches = preg_match("/^[a-zA-Z0-9\,\s]+$/", $suffixes_string);
        return self::$refinery->kindlyTo()->bool()->transform($matches);
    }

    final public function hasSuffixInputNoDuplicatesToItsOwnEntries(array $a_suffixes): bool
    {
        return count($a_suffixes) === count(array_unique($a_suffixes));
    }

    final public function causesNoActiveSuffixesConflict(
        array $a_future_suffixes,
        bool $a_future_activation_state,
        Icon $a_current_icon
    ): bool {
        //if the icon is not going to be activated there can be no suffix conflict with other icons
        if (!$a_future_activation_state) {
            return true;
        }

        $existing_icons = $this->getIcons();
        //remove current icon from existing icon array to prevent validation errors when updating an existing icon
        if (!$a_current_icon instanceof NullIcon) {
            unset($existing_icons[$a_current_icon->getRid()]);
        }

        $duplicate_suffixes = [];
        foreach ($existing_icons as $existing_icon) {
            //skip deactivated icons as having multiple icon entries for the same suffix is allowed, the restriction is that only one can be activated
            if (!$existing_icon->isActive()) {
                continue;
            }
            $duplicate_suffixes = array_merge(
                $duplicate_suffixes,
                array_intersect($a_future_suffixes, $existing_icon->getSuffixes())
            );
        }

        return $duplicate_suffixes === [];
    }
}
