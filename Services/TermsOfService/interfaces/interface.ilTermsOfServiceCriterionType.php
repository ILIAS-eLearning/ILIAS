<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Interface ilTermsOfServiceCriterionType
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceCriterionType
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
     * @param \ilObjUser $user
     * @param \ilTermsOfServiceCriterionConfig $config
     * @return bool
     */
    public function evaluate(\ilObjUser $user, \ilTermsOfServiceCriterionConfig $config) : bool;

    /**
     * @param \ilLanguage $lng
     * @return \ilTermsOfServiceCriterionTypeGUI
     */
    public function ui(\ilLanguage $lng) : \ilTermsOfServiceCriterionTypeGUI;
}
