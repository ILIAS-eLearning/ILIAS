<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTermsOfServiceUserHasGlobalRoleCriterion
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasGlobalRoleCriterion implements \ilTermsOfServiceCriterionType
{
    /** @var \ilRbacReview */
    protected $rbacReview;

    /** @var \ilObjectDataCache */
    protected $objectCache;

    /**
     * ilTermsOfServiceUserHasGlobalRoleCriterion constructor.
     * @param ilRbacReview $rbacReview
     * @param ilObjectDataCache $objectCache
     */
    public function __construct(\ilRbacReview $rbacReview, \ilObjectDataCache $objectCache)
    {
        $this->rbacReview = $rbacReview;
        $this->objectCache = $objectCache;
    }

    /**
     * @inheritdoc
     */
    public function getTypeIdent() : string
    {
        return 'usr_global_role';
    }

    /**
     * @inheritdoc
     */
    public function hasUniqueNature() : bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function evaluate(\ilObjUser $user, \ilTermsOfServiceCriterionConfig $config) : bool
    {
        $roleId = $config['role_id'] ?? 0;

        if (!is_numeric($roleId) || $roleId < 1 || is_float($roleId)) {
            return false;
        }

        if (!$this->rbacReview->isGlobalRole($roleId)) {
            return false;
        }

        $result = $this->rbacReview->isAssigned($user->getId(), $roleId);

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function ui(\ilLanguage $lng) : \ilTermsOfServiceCriterionTypeGUI
    {
        return new \ilTermsOfServiceUserHasGlobalRoleCriterionGUI($this, $lng, $this->rbacReview, $this->objectCache);
    }
}
