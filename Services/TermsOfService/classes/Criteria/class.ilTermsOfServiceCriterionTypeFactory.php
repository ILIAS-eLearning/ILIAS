<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceCriterionTypeFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceCriterionTypeFactory implements ilTermsOfServiceCriterionTypeFactoryInterface
{
    /** @var ilTermsOfServiceCriterionType[] */
    protected $types = [];

    /**
     * ilTermsOfServiceCriterionTypeFactory constructor.
     * @param ilRbacReview $rbacReview
     * @param ilObjectDataCache $
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

    /**
     * @inheritdoc
     */
    public function getTypesByIdentMap() : array
    {
        return $this->types;
    }

    /**
     * @inheritdoc
     */
    public function findByTypeIdent(string $typeIdent, bool $useFallback = false) : ilTermsOfServiceCriterionType
    {
        if (isset($this->types[$typeIdent])) {
            return $this->types[$typeIdent];
        }

        if ($useFallback) {
            return new ilTermsOfServiceNullCriterion();
        }

        throw new ilTermsOfServiceCriterionTypeNotFoundException(sprintf(
            "Did not find criterion type by ident: %s",
            var_export($typeIdent, true)
        ));
    }
}
