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
use ILIAS\LegalDocuments\Condition\Role;
use ILIAS\LegalDocuments\Value\CriterionContent;
use ILIAS\LegalDocuments\ConsumerToolbox\UI;
use ILIAS\UI\Component\Input\Field\Group;
use ilObjectDataCache;
use ilRbacReview;
use Closure;

class RoleDefinition implements ConditionDefinition
{
    /**
     * @param Closure(array<string, mixed>): Constraint $required
     */
    public function __construct(
        private readonly UI $ui,
        private readonly ilObjectDataCache $cache,
        private readonly ilRbacReview $review,
        private readonly Closure $required
    ) {
    }

    public function formGroup(array $arguments = []): Group
    {
        $roles = $this->review->getGlobalRoles();
        $roles = array_combine($roles, array_map($this->translatedRole(...), $roles));
        $default_role = isset($roles[SYSTEM_ROLE_ID]) ? SYSTEM_ROLE_ID : key($roles);

        return $this->ui->create()->input()->field()->group([
            'role_id' => $this->radio(
                $this->ui->txt('perm_global_role'),
                $roles,
                $arguments['role_id'] ?? $default_role
            )->withRequired(true, ($this->required)($roles)),
        ], $this->ui->txt('crit_type_usr_global_role'), $this->ui->txt('crit_type_usr_global_role_info'));
    }

    public function translatedRole(int $role): string
    {
        return $this->cache->lookupTitle($role);
    }

    public function translatedType(): string
    {
        return $this->ui->txt('crit_type_usr_global_role');
    }

    public function withCriterion(CriterionContent $criterion): Condition
    {
        return new Role($criterion, $this, $this->ui->create(), $this->review);
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
