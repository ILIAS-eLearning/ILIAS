<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilAccessibilityCriterionType
 */
interface ilAccessibilityCriterionType
{
    /**
     * Returns a unique id of the criterion type
     * @return string
     */
    public function getTypeIdent() : string;

    /**
     * Returns whether or not a criterion is unique by it's nature.
     * Example: "User Language". A user account can only have one profile language .
     * @return bool
     */
    public function hasUniqueNature() : bool;

    /**
     * @param ilObjUser                       $user
     * @param ilAccessibilityCriterionConfig $config
     * @return bool
     */
    public function evaluate(ilObjUser $user, ilAccessibilityCriterionConfig $config) : bool;

    /**
     * @param ilLanguage $lng
     * @return ilAccessibilityCriterionTypeGUI
     */
    public function ui(ilLanguage $lng) : ilAccessibilityCriterionTypeGUI;
}
