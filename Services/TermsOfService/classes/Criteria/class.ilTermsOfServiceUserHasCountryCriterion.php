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
 * Class ilTermsOfServiceUserHasCountryCriterion
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasCountryCriterion implements ilTermsOfServiceCriterionType
{
    /**
     * ilTermsOfServiceUserHasCountryCriterion constructor.
     * @param string[] $countryCodes
     */
    public function __construct(protected array $countryCodes)
    {
    }

    public function getTypeIdent(): string
    {
        return 'usr_country';
    }

    public function hasUniqueNature(): bool
    {
        return true;
    }

    public function evaluate(ilObjUser $user, ilTermsOfServiceCriterionConfig $config): bool
    {
        $country = $config['country'] ?? '';

        if (!is_string($country) || 2 !== strlen($country) || !is_string($user->getSelectedCountry())) {
            return false;
        }

        return strtolower($country) === strtolower($user->getSelectedCountry());
    }

    public function ui(ilLanguage $lng): ilTermsOfServiceCriterionTypeGUI
    {
        return new ilTermsOfServiceUserHasCountryCriterionGUI($this, $lng, $this->countryCodes);
    }
}
