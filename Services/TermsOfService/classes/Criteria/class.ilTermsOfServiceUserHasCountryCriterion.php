<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceUserHasCountryCriterion
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasCountryCriterion implements ilTermsOfServiceCriterionType
{
    /** @var string[] */
    protected $countryCodes = [];

    /**
     * ilTermsOfServiceUserHasCountryCriterion constructor.
     * @param string[] $countryCodes
     */
    public function __construct(array $countryCodes)
    {
        $this->countryCodes = $countryCodes;
    }

    /**
     * @inheritdoc
     */
    public function getTypeIdent() : string
    {
        return 'usr_country';
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
    public function evaluate(ilObjUser $user, ilTermsOfServiceCriterionConfig $config) : bool
    {
        $country = $config['country'] ?? '';

        if (!is_string($country) || 2 !== strlen($country) || !is_string($user->getSelectedCountry())) {
            return false;
        }

        $result = strtolower($country) === strtolower($user->getSelectedCountry());

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function ui(ilLanguage $lng) : ilTermsOfServiceCriterionTypeGUI
    {
        return new ilTermsOfServiceUserHasCountryCriterionGUI($this, $lng, $this->countryCodes);
    }
}
