<?php declare(strict_types=1);
/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\UI\Component\Component;
use ILIAS\UI\Factory;

/**
 * Class ilTermsOfServiceUserHasCountryCriterionGUI
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilTermsOfServiceUserHasCountryCriterionGUI implements ilTermsOfServiceCriterionTypeGUI
{
    /** @var ilTermsOfServiceUserHasCountryCriterion */
    protected $type;

    /** @var ilLanguage */
    protected $lng;

    /** @var string[] */
    protected $countryCodes = [];

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

    /**
     * @inheritdoc
     */
    public function appendOption(ilRadioGroupInputGUI $group, ilTermsOfServiceCriterionConfig $config) : void
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

    /**
     * @inheritdoc
     */
    public function getConfigByForm(ilPropertyFormGUI $form) : ilTermsOfServiceCriterionConfig
    {
        $config = new ilTermsOfServiceCriterionConfig([
            'country' => (string) $form->getInput($this->type->getTypeIdent() . '_country')
        ]);

        return $config;
    }

    /**
     * @inheritdoc
     */
    public function getIdentPresentation() : string
    {
        return $this->lng->txt('tos_crit_type_usr_country');
    }

    /**
     * @inheritdoc
     */
    public function getValuePresentation(ilTermsOfServiceCriterionConfig $config, Factory $uiFactory) : Component
    {
        $country = $config['country'] ?? '';

        if (!is_string($country) || 2 !== strlen($country)) {
            return $uiFactory->legacy('');
        }

        return $uiFactory->legacy($this->lng->txt('meta_c_' . strtoupper((string) $country)));
    }
}
