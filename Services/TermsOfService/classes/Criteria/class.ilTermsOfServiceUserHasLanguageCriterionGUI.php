<?php
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Class ilTermsOfServiceUserHasLanguageCriterionGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasLanguageCriterionGUI implements \ilTermsOfServiceCriterionTypeGUI
{
    /** @var \ilTermsOfServiceUserHasLanguageCriterion */
    protected $type;

    /** @var \ilLanguage */
    protected $lng;

    /**
     * ilTermsOfServiceUserHasLanguageCriterionGUI constructor.
     * @param ilTermsOfServiceUserHasLanguageCriterion $type
     * @param ilLanguage $lng
     */
    public function __construct(
        \ilTermsOfServiceUserHasLanguageCriterion $type,
        \ilLanguage $lng
    ) {
        $this->type = $type;
        $this->lng = $lng;
    }

    /**
     * @inheritdoc
     */
    public function appendOption(\ilRadioGroupInputGUI $group, \ilTermsOfServiceCriterionConfig $config)
    {
        $option = new \ilRadioOption($this->getIdentPresentation(), $this->type->getTypeIdent());
        $option->setInfo($this->lng->txt('tos_crit_type_usr_language_info'));

        $languageSelection = new \ilSelectInputGUI(
            $this->lng->txt('language'),
            $this->type->getTypeIdent() . '_lng'
        );
        $languageSelection->setRequired(true);

        $options = [];
        foreach ($this->lng->getInstalledLanguages() as $lng) {
            $options[$lng] = $this->lng->txt('meta_l_' . $lng);
        }

        asort($options);

        $languageSelection->setOptions(['' => $this->lng->txt('please_choose')] + $options);
        $languageSelection->setValue((string) ($config['lng'] ?? ''));

        $option->addSubItem($languageSelection);

        $group->addOption($option);
    }

    /**
     * @inheritdoc
     */
    public function getConfigByForm(\ilPropertyFormGUI $form) : \ilTermsOfServiceCriterionConfig
    {
        $config = new \ilTermsOfServiceCriterionConfig([
            'lng' => (string) $form->getInput($this->type->getTypeIdent() . '_lng')
        ]);

        return $config;
    }

    /**
     * @inheritdoc
     */
    public function getIdentPresentation() : string
    {
        return $this->lng->txt('tos_crit_type_usr_language');
    }

    /**
     * @inheritdoc
     */
    public function getValuePresentation(\ilTermsOfServiceCriterionConfig $config, Factory $uiFactory) : Component
    {
        $lng = $config['lng'] ?? '';

        if (!is_string($lng) || 2 !== strlen($lng)) {
            return $uiFactory->legacy('');
        }

        return $uiFactory->legacy($this->lng->txt('meta_l_' . (string) $lng));
    }
}
