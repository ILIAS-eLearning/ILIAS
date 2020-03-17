<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAccessibilityCriterionTypeFactory
 */
class ilAccessibilityCriterionTypeFactory implements ilAccessibilityCriterionTypeFactoryInterface
{
    /** @var ilAccessibilityCriterionType[] */
    protected $types = [];

    /**
     * ilAccessibilityCriterionTypeFactory constructor.
     * @param ilRbacReview      $rbacReview
     * @param ilObjectDataCache $objectCache
     */
    public function __construct(ilRbacReview $rbacReview, ilObjectDataCache $objectCache)
    {
        $usrLanguageCriterion = new ilAccessibilityUserHasLanguageCriterion();

        $this->types = [
            $usrLanguageCriterion->getTypeIdent() => $usrLanguageCriterion
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
     * @inheritDoc
     */
    public function hasOnlyOneCriterion() : bool
    {
        if (count($this->types) == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function findByTypeIdent(string $typeIdent, bool $useFallback = false) : ilAccessibilityCriterionType
    {
        if (isset($this->types[$typeIdent])) {
            return $this->types[$typeIdent];
        }

        if ($useFallback) {
            return new ilAccessibilityNullCriterion();
        }

        throw new ilAccessibilityCriterionTypeNotFoundException(sprintf(
            "Did not find criterion type by ident: %s",
            var_export($typeIdent, true)
        ));
    }
}
