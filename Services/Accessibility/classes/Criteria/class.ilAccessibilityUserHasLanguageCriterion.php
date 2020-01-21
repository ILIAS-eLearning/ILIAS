<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAccessibilityUserHasLanguageCriterion
 */
class ilAccessibilityUserHasLanguageCriterion implements ilAccessibilityCriterionType
{
    /**
     * @inheritdoc
     */
    public function getTypeIdent() : string
    {
        return 'usr_language';
    }

    /**
     * @inheritdoc
     */
    public function hasUniqueNature() : bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function evaluate(ilObjUser $user, ilAccessibilityCriterionConfig $config) : bool
    {
        $lng = $config['lng'] ?? '';

        if (!is_string($lng) || 2 !== strlen($lng)) {
            return false;
        }

        $result = strtolower($lng) === strtolower($user->getLanguage());

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function ui(ilLanguage $lng) : ilAccessibilityCriterionTypeGUI
    {
        return new ilAccessibilityUserHasLanguageCriterionGUI($this, $lng);
    }
}
