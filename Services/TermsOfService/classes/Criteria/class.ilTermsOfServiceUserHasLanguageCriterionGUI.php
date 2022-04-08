<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Class ilTermsOfServiceUserHasLanguageCriterionGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasLanguageCriterionGUI implements ilTermsOfServiceCriterionTypeGUI
{
    protected ilTermsOfServiceUserHasLanguageCriterion $type;
    protected ilLanguage $lng;

    public function __construct(
        ilTermsOfServiceUserHasLanguageCriterion $type,
        ilLanguage $lng
    ) {
        $this->type = $type;
        $this->lng = $lng;
    }
    // PHP8-Review: Parameter's name changed during inheritance from 'option' to 'group'
    public function appendOption(ilRadioGroupInputGUI $group, ilTermsOfServiceCriterionConfig $config) : void
    {
        $option = new ilRadioOption($this->getIdentPresentation(), $this->type->getTypeIdent());
        $option->setInfo($this->lng->txt('tos_crit_type_usr_language_info'));

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

    public function getConfigByForm(ilPropertyFormGUI $form) : ilTermsOfServiceCriterionConfig
    {
        $config = new ilTermsOfServiceCriterionConfig([
            'lng' => (string) $form->getInput($this->type->getTypeIdent() . '_lng')
        ]);

        return $config;
    }

    public function getIdentPresentation() : string
    {
        return $this->lng->txt('tos_crit_type_usr_language');
    }

    public function getValuePresentation(ilTermsOfServiceCriterionConfig $config, Factory $uiFactory) : Component
    {
        $lng = $config['lng'] ?? '';

        if (!is_string($lng) || 2 !== strlen($lng)) {
            return $uiFactory->legacy('');
        }

        return $uiFactory->legacy($this->lng->txt('meta_l_' . $lng));
    }
}
