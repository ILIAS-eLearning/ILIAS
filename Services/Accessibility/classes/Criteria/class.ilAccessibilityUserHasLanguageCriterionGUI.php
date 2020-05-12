<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Class ilAccessibilityUserHasLanguageCriterionGUI
 */
class ilAccessibilityUserHasLanguageCriterionGUI implements ilAccessibilityCriterionTypeGUI
{
    /** @var ilAccessibilityUserHasLanguageCriterion */
    protected $type;

    /** @var ilLanguage */
    protected $lng;

    /**
     * ilAccessibilityUserHasLanguageCriterionGUI constructor.
     * @param ilAccessibilityUserHasLanguageCriterion $type
     * @param ilLanguage                               $lng
     */
    public function __construct(
        ilAccessibilityUserHasLanguageCriterion $type,
        ilLanguage $lng
    ) {
        $this->type = $type;
        $this->lng = $lng;
    }

    /**
     * @inheritdoc
     */
    public function appendOption(ilRadioGroupInputGUI $group, ilAccessibilityCriterionConfig $config) : void
    {
        $option = new ilRadioOption($this->getIdentPresentation(), $this->type->getTypeIdent());

        $languageSelection = new ilSelectInputGUI(
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
     * @inheritDoc
     */
    public function getSelection(ilAccessibilityCriterionConfig $config) : ilSelectInputGUI
    {
        $languageSelection = new ilSelectInputGUI(
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

        return $languageSelection;
    }

    /**
     * @inheritdoc
     */
    public function getConfigByForm(ilPropertyFormGUI $form) : ilAccessibilityCriterionConfig
    {
        $config = new ilAccessibilityCriterionConfig([
            'lng' => (string) $form->getInput($this->type->getTypeIdent() . '_lng')
        ]);

        return $config;
    }

    /**
     * @inheritdoc
     */
    public function getIdentPresentation() : string
    {
        return $this->lng->txt('acc_crit_type_usr_language');
    }

    /**
     * @inheritdoc
     */
    public function getValuePresentation(ilAccessibilityCriterionConfig $config, Factory $uiFactory) : Component
    {
        $lng = $config['lng'] ?? '';

        if (!is_string($lng) || 2 !== strlen($lng)) {
            return $uiFactory->legacy('');
        }

        return $uiFactory->legacy($this->lng->txt('meta_l_' . (string) $lng));
    }
}
