<?php

declare(strict_types=1);

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
 * Class ilTermsOfServiceUserHasLanguageCriterion
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasLanguageCriterion implements ilTermsOfServiceCriterionType
{
    public function getTypeIdent(): string
    {
        return 'usr_language';
    }

    public function hasUniqueNature(): bool
    {
        return true;
    }

    public function evaluate(ilObjUser $user, ilTermsOfServiceCriterionConfig $config): bool
    {
        $lng = $config['lng'] ?? '';

        if (!is_string($lng) || 2 !== strlen($lng) || !is_string($user->getLanguage())) {
            return false;
        }

        return strtolower($lng) === strtolower($user->getLanguage());
    }

    public function ui(ilLanguage $lng): ilTermsOfServiceCriterionTypeGUI
    {
        return new ilTermsOfServiceUserHasLanguageCriterionGUI($this, $lng);
    }
}
