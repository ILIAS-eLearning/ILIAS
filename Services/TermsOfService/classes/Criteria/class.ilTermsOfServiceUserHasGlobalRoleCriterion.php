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
 * Class ilTermsOfServiceUserHasGlobalRoleCriterion
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasGlobalRoleCriterion implements ilTermsOfServiceCriterionType
{
    protected ilRbacReview $rbacReview;
    protected ilObjectDataCache $objectCache;

    public function __construct(ilRbacReview $rbacReview, ilObjectDataCache $objectCache)
    {
        $this->rbacReview = $rbacReview;
        $this->objectCache = $objectCache;
    }

    public function getTypeIdent(): string
    {
        return 'usr_global_role';
    }

    public function hasUniqueNature(): bool
    {
        return false;
    }

    public function evaluate(ilObjUser $user, ilTermsOfServiceCriterionConfig $config): bool
    {
        $roleId = $config['role_id'] ?? 0;

        if (!is_numeric($roleId) || $roleId < 1 || $roleId > PHP_INT_MAX || is_float($roleId)) {
            return false;
        }

        if (!$this->rbacReview->isGlobalRole((int) $roleId)) {
            return false;
        }

        return $this->rbacReview->isAssigned($user->getId(), (int) $roleId);
    }

    public function ui(ilLanguage $lng): ilTermsOfServiceCriterionTypeGUI
    {
        return new ilTermsOfServiceUserHasGlobalRoleCriterionGUI($this, $lng, $this->rbacReview, $this->objectCache);
    }
}
