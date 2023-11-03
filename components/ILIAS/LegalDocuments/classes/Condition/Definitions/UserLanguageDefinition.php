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
use ILIAS\LegalDocuments\Condition;
use ILIAS\LegalDocuments\ConditionDefinition;
use ILIAS\LegalDocuments\Condition\UserLanguage;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\UI\Component\Input\Field\Group;
use Closure;

class UserLanguageDefinition implements ConditionDefinition
{
    /**
     * @param list<string> $installed_languages
     * @param Closure(array<string, mixed>): Constraint $required
     */
    public function __construct(
        private readonly UI $ui,
        private readonly array $installed_languages,
        private readonly Closure $required
    ) {
    }

    public function formGroup(array $arguments = []): Group
    {
        $languages = array_combine($this->installed_languages, array_map($this->translatedLanguage(...), $this->installed_languages));

        return $this->ui->create()->input()->field()->group([
            'lng' => $this->radio(
                $this->ui->txt('language'),
                $languages,
                $arguments['lng'] ?? null
            )->withRequired(true, ($this->required)($languages))
        ], $this->ui->txt('crit_type_usr_language'), $this->ui->txt('crit_type_usr_language_info'));
    }

    public function withCriterion(CriterionContent $criterion): Condition
    {
        return new UserLanguage($criterion, $this, $this->ui->create());
    }

    public function translatedType(): string
    {
        return $this->ui->txt('crit_type_usr_language');
    }

    public function translatedLanguage(string $language): string
    {
        return $this->ui->txt('meta_l_' . $language);
    }

    /**
     * @param array<string, string> $options
     */
    private function radio($lang_key, array $options, $value)
    {
        $field = $this->ui->create()->input()->field()->radio($lang_key);
        foreach ($options as $key => $label) {
            $field = $field->withOption((string) $key, $label);
        }
        return $value === null ? $field : $field->withValue($value);
    }
}
