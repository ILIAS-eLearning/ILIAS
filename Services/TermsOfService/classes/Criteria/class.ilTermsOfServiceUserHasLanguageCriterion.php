<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceUserHasLanguageCriterion
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasLanguageCriterion implements ilTermsOfServiceCriterionType
{
    public function getTypeIdent() : string
    {
        return 'usr_language';
    }

    public function hasUniqueNature() : bool
    {
        return true;
    }

    public function evaluate(ilObjUser $user, ilTermsOfServiceCriterionConfig $config) : bool
    {
        $lng = $config['lng'] ?? '';

        if (!is_string($lng) || 2 !== strlen($lng) || !is_string($user->getLanguage())) {
            return false;
        }

        return strtolower($lng) === strtolower($user->getLanguage());
    }

    public function ui(ilLanguage $lng) : ilTermsOfServiceCriterionTypeGUI
    {
        return new ilTermsOfServiceUserHasLanguageCriterionGUI($this, $lng);
    }
}
