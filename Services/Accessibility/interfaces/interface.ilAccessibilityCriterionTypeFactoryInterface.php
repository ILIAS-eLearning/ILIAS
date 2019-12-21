<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilAccessibilityCriterionTypeFactoryInterface
 */
interface ilAccessibilityCriterionTypeFactoryInterface
{
    /**
     * @return ilAccessibilityCriterionType[]
     */
    public function getTypesByIdentMap() : array;

    /**
     * @return bool
     */
    public function hasOnlyOneCriterion() : bool;

    /**
     * @param string $typeIdent
     * @param bool   $useFallback
     * @return ilAccessibilityCriterionType
     * @throws ilAccessibilityCriterionTypeNotFoundException
     */
    public function findByTypeIdent(string $typeIdent, bool $useFallback = false) : ilAccessibilityCriterionType;
}
