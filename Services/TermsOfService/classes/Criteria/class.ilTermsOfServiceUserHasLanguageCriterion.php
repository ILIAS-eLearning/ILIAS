<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceUserHasLanguageCriterion
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasLanguageCriterion implements \ilTermsOfServiceCriterionType
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
    public function evaluate(\ilObjUser $user, \ilTermsOfServiceCriterionConfig $config) : bool
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
    public function ui(\ilLanguage $lng) : \ilTermsOfServiceCriterionTypeGUI
    {
        return new \ilTermsOfServiceUserHasLanguageCriterionGUI($this, $lng);
    }
}
