<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceCriterionTypeFactoryInterface
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceCriterionTypeFactoryInterface
{
    /**
     * @return \ilTermsOfServiceCriterionType[]
     */
    public function getTypesByIdentMap() : array;

    /**
     * @param string $typeIdent
     * @param bool $useFallback
     * @return ilTermsOfServiceCriterionType
     * @throws \ilTermsOfServiceCriterionTypeNotFoundException
     */
    public function findByTypeIdent(string $typeIdent, bool $useFallback = false) : \ilTermsOfServiceCriterionType;
}
