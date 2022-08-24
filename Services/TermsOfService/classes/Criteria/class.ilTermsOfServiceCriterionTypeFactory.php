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
 * Class ilTermsOfServiceCriterionTypeFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceCriterionTypeFactory implements ilTermsOfServiceCriterionTypeFactoryInterface
{
    /** @var ilTermsOfServiceCriterionType[] */
    protected array $types = [];

    /**
     * ilTermsOfServiceCriterionTypeFactory constructor.
     * @param ilRbacReview $rbacReview
     * @param ilObjectDataCache $objectCache
     * @param string[] $countryCodes
     */
    public function __construct(ilRbacReview $rbacReview, ilObjectDataCache $objectCache, array $countryCodes)
    {
        $usrLanguageCriterion = new ilTermsOfServiceUserHasLanguageCriterion();
        $usrGlobalRoleCriterion = new ilTermsOfServiceUserHasGlobalRoleCriterion($rbacReview, $objectCache);
        $usrCountryCriterion = new ilTermsOfServiceUserHasCountryCriterion($countryCodes);

        $this->types = [
            $usrLanguageCriterion->getTypeIdent() => $usrLanguageCriterion,
            $usrGlobalRoleCriterion->getTypeIdent() => $usrGlobalRoleCriterion,
            $usrCountryCriterion->getTypeIdent() => $usrCountryCriterion,
        ];
    }

    public function getTypesByIdentMap(): array
    {
        return $this->types;
    }

    public function findByTypeIdent(string $typeIdent, bool $useFallback = false): ilTermsOfServiceCriterionType
    {
        if (isset($this->types[$typeIdent])) {
            return $this->types[$typeIdent];
        }

        if ($useFallback) {
            return new ilTermsOfServiceNullCriterion();
        }

        throw new ilTermsOfServiceCriterionTypeNotFoundException(sprintf(
            'Did not find criterion type by ident: %s',
            var_export($typeIdent, true)
        ));
    }
}
