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
 * Class ilAccessibilityCriterionTypeFactory
 */
class ilAccessibilityCriterionTypeFactory implements ilAccessibilityCriterionTypeFactoryInterface
{
    /** @var ilAccessibilityCriterionType[] */
    protected array $types = [];

    public function __construct(ilRbacReview $rbacReview, ilObjectDataCache $objectCache)
    {
        $usrLanguageCriterion = new ilAccessibilityUserHasLanguageCriterion();

        $this->types = [
            $usrLanguageCriterion->getTypeIdent() => $usrLanguageCriterion
        ];
    }

    public function getTypesByIdentMap() : array
    {
        return $this->types;
    }

    public function hasOnlyOneCriterion() : bool
    {
        if (count($this->types) == 1) {
            return true;
        } else {
            return false;
        }
    }

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
