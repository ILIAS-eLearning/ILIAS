<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Class ilTermsOfServiceUserHasGlobalRoleCriterionGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasGlobalRoleCriterionGUI implements \ilTermsOfServiceCriterionTypeGUI
{
    /**
     * @var \ilTermsOfServiceUserHasGlobalRoleCriterion
     */
    protected $type;

    /**
     * @var \ilRbacReview
     */
    protected $rbacReview;

    /**
     * @var \ilLanguage
     */
    protected $lng;

    /**
     * @var \ilObjectDataCache
     */
    protected $objectCache;

    /**
     * ilTermsOfServiceUserHasGlobalRoleCriterionGUI constructor.
     * @param \ilTermsOfServiceUserHasGlobalRoleCriterion $type
     * @param \ilLanguage $lng
     * @param \ilRbacReview $rbacReview
     * @param \ilObjectDataCache $objectCache
     */
    public function __construct(
        \ilTermsOfServiceUserHasGlobalRoleCriterion $type,
        \ilLanguage $lng,
        \ilRbacReview $rbacReview,
        \ilObjectDataCache $objectCache
    ) {
        $this->type = $type;
        $this->lng = $lng;
        $this->rbacReview = $rbacReview;
        $this->objectCache = $objectCache;

        $this->lng->loadLanguageModule('rbac');
    }

    /**
     * @inheritdoc
     */
    public function appendOption(\ilRadioGroupInputGUI $group, \ilTermsOfServiceCriterionConfig $config)
    {
        $option = new \ilRadioOption($this->getIdentPresentation(), $this->type->getTypeIdent());
        $option->setInfo($this->lng->txt('tos_crit_type_usr_global_role_info'));

        $roleSelection = new \ilSelectInputGUI(
            $this->lng->txt('perm_global_role'),
            $this->type->getTypeIdent() . '_role_id'
        );
        $roleSelection->setRequired(true);

        $options = [];
        foreach ($this->rbacReview->getGlobalRoles() as $roleId) {
            $options[$roleId] = $this->objectCache->lookupTitle($roleId);
        }

        asort($options);

        $roleSelection->setOptions(['' => $this->lng->txt('please_choose')] + $options);
        $roleSelection->setValue((int) ($config['role_id'] ?? 0));

        $option->addSubItem($roleSelection);

        $group->addOption($option);
    }

    /**
     * @inheritdoc
     */
    public function getConfigByForm(\ilPropertyFormGUI $form) : \ilTermsOfServiceCriterionConfig
    {
        $config = new \ilTermsOfServiceCriterionConfig([
            'role_id' => (int) $form->getInput($this->type->getTypeIdent() . '_role_id')
        ]);

        return $config;
    }

    /**
     * @inheritdoc
     */
    public function getIdentPresentation() : string
    {
        return $this->lng->txt('tos_crit_type_usr_global_role');
    }

    /**
     * @inheritdoc
     */
    public function getValuePresentation(\ilTermsOfServiceCriterionConfig $config, Factory $uiFactory) : Component
    {
        $roleId = $config['role_id'] ?? 0;

        if (!is_numeric($roleId) || $roleId < 1 || is_float($roleId)) {
            return $uiFactory->legacy('');
        }

        return $uiFactory->legacy($this->objectCache->lookupTitle($roleId));
    }
}
