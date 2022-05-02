<?php declare(strict_types=1);

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
