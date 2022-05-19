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

/**
 * Class ilAccessibilityUserHasLanguageCriterion
 */
class ilAccessibilityUserHasLanguageCriterion implements ilAccessibilityCriterionType
{
    public function getTypeIdent() : string
    {
        return 'usr_language';
    }

    public function hasUniqueNature() : bool
    {
        return true;
    }

    public function evaluate(ilObjUser $user, ilAccessibilityCriterionConfig $config) : bool
    {
        $lng = $config['lng'] ?? '';

        if (!is_string($lng) || 2 !== strlen($lng)) {
            return false;
        }

        $result = strtolower($lng) === strtolower($user->getLanguage());

        return $result;
    }

    public function ui(ilLanguage $lng) : ilAccessibilityCriterionTypeGUI
    {
        return new ilAccessibilityUserHasLanguageCriterionGUI($this, $lng);
    }
}
