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
 * Interface ilTermsOfServiceCriterionType
 * @author Michael Jansen <mjansen@databay.de>
 */
interface ilTermsOfServiceCriterionType
{
    /**
     * Returns a unique id of the criterion type
     */
    public function getTypeIdent(): string;

    /**
     * Returns whether or not a criterion is unique by it's nature.
     * Example: "User Language". A user account can only have one profile language .
     */
    public function hasUniqueNature(): bool;

    public function evaluate(ilObjUser $user, ilTermsOfServiceCriterionConfig $config): bool;

    public function ui(ilLanguage $lng): ilTermsOfServiceCriterionTypeGUI;
}
