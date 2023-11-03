<?php

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

declare(strict_types=1);

namespace ILIAS\LegalDocuments\Condition\Definition;

use ILIAS\Refinery\Constraint;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\LegalDocuments\Condition;
use ILIAS\LegalDocuments\ConditionDefinition;
use ILIAS\LegalDocuments\Condition\UserCountry;
use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\UI\Component\Input\Field\Group;
use ilCountry;
use Closure;

class UserCountryDefinition implements ConditionDefinition
{
    /**
     * @param Closure(array<string, mixed>): Constraint $required
     */
    public function __construct(private readonly UI $ui, private readonly Closure $required)
    {
    }

    public function formGroup(array $arguments = []): Group
    {
        $countries = ilCountry::getCountryCodes();
        $countries = array_combine($countries, array_map($this->translatedCountry(...), $countries));

        return $this->ui->create()->input()->field()->group([
            'country' => $this->ui->create()->input()->field()->select(
                $this->ui->txt('country'),
                $countries
            )->withRequired(true, ($this->required)($countries))->withValue(strtoupper($arguments['country'] ?? '') ?: null)
        ], $this->ui->txt('crit_type_usr_country'), $this->ui->txt('crit_type_usr_country_info'));
    }

    public function withCriterion(CriterionContent $criterion): Condition
    {
        return new UserCountry($criterion, $this, $this->ui->create());
    }

    public function translatedType(): string
    {
        return $this->ui->txt('crit_type_usr_country');
    }

    public function translatedCountry(string $country): string
    {
        return $this->ui->txt('meta_c_' . strtoupper($country));
    }
}
