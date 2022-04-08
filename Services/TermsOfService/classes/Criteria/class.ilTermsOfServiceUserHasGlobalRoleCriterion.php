<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

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

    public function getTypeIdent() : string
    {
        return 'usr_global_role';
    }

    public function hasUniqueNature() : bool
    {
        return false;
    }

    public function evaluate(ilObjUser $user, ilTermsOfServiceCriterionConfig $config) : bool
    {
        $roleId = $config['role_id'] ?? 0;

        if (!is_numeric($roleId) || $roleId < 1 || $roleId > PHP_INT_MAX || is_float($roleId)) {
            return false;
        }

        if (!$this->rbacReview->isGlobalRole($roleId)) {
            return false;
        }

        return $this->rbacReview->isAssigned($user->getId(), $roleId);
    }

    public function ui(ilLanguage $lng) : ilTermsOfServiceCriterionTypeGUI
    {
        return new ilTermsOfServiceUserHasGlobalRoleCriterionGUI($this, $lng, $this->rbacReview, $this->objectCache);
    }
}
