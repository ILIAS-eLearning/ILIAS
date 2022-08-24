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

use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Class ilTermsOfServiceUserHasCountryCriterionGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasCountryCriterionGUI implements ilTermsOfServiceCriterionTypeGUI
{
    protected ilTermsOfServiceUserHasCountryCriterion $type;
    protected ilLanguage $lng;
    /** @var string[] */
    protected array $countryCodes = [];

    /**
     * ilTermsOfServiceUserHasLanguageCriterionGUI constructor.
     * @param ilTermsOfServiceUserHasCountryCriterion $type
     * @param ilLanguage $lng
     * @param string[] $countryCodes
     */
    public function __construct(
        ilTermsOfServiceUserHasCountryCriterion $type,
        ilLanguage $lng,
        array $countryCodes
    ) {
        $this->type = $type;
        $this->lng = $lng;
        $this->countryCodes = $countryCodes;
    }

    public function appendOption(ilRadioGroupInputGUI $group, ilTermsOfServiceCriterionConfig $config): void
    {
        $option = new ilRadioOption($this->getIdentPresentation(), $this->type->getTypeIdent());
        $option->setInfo($this->lng->txt('tos_crit_type_usr_country_info'));

        $countrySelection = new ilSelectInputGUI(
            $this->lng->txt('country'),
            $this->type->getTypeIdent() . '_country'
        );
        $countrySelection->setRequired(true);

        $options = [];
        foreach ($this->countryCodes as $country) {
            $options[strtolower($country)] = $this->lng->txt('meta_c_' . strtoupper($country));
        }
        asort($options);

        $countrySelection->setOptions(['' => $this->lng->txt('please_choose')] + $options);
        $countrySelection->setValue((string) ($config['country'] ?? ''));

        $option->addSubItem($countrySelection);

        $group->addOption($option);
    }

    public function getConfigByForm(ilPropertyFormGUI $form): ilTermsOfServiceCriterionConfig
    {
        $config = new ilTermsOfServiceCriterionConfig([
            'country' => (string) $form->getInput($this->type->getTypeIdent() . '_country')
        ]);

        return $config;
    }

    public function getIdentPresentation(): string
    {
        return $this->lng->txt('tos_crit_type_usr_country');
    }

    public function getValuePresentation(ilTermsOfServiceCriterionConfig $config, Factory $uiFactory): Component
    {
        $country = $config['country'] ?? '';

        if (!is_string($country) || 2 !== strlen($country)) {
            return $uiFactory->legacy('');
        }

        return $uiFactory->legacy($this->lng->txt('meta_c_' . strtoupper($country)));
    }
}
