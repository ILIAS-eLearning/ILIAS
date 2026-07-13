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

namespace ILIAS\Registration;

use ilObject;
use ilDateTime;
use ilLanguage;
use ilUIService;
use ilRbacReview;
use ilDatePresentation;
use ilRegistrationCode;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Component\Input\Field\Factory;
use ILIAS\UI\Component\Input\Container\Filter\Standard as FilterComponent;
use ILIAS\UI\Component\Input\Container\Filter\FilterInput;

class RegistrationFilterComponent
{
    protected FilterComponent $filter;

    public function __construct(
        private readonly string $action,
        private readonly UIFactory $ui_factory,
        private readonly ilUIService $ui_service,
        private readonly ilLanguage $lng,
        private readonly ilRbacReview $rbac_review,
        private readonly RegistrationCodeRepository $code_repository,
    ) {
        $filter_inputs = [];
        $is_input_initially_rendered = [];
        $field_factory = $this->ui_factory->input()->field();

        foreach ($this->getFilterFields($field_factory) as $filter_id => $filter) {
            [$filter_inputs[$filter_id], $is_input_initially_rendered[$filter_id]] = $filter;
        }

        $this->filter = $this->ui_service->filter()->standard(
            'participant_filter',
            $this->action,
            $filter_inputs,
            $is_input_initially_rendered,
            true,
            true
        );
    }

    public function getFilterComponent(): FilterComponent
    {
        return $this->filter;
    }

    public function getFilterData(): CodeFilter
    {
        return (new CodeFilter())->withData($this->ui_service->filter()->getData($this->filter));
    }

    /**
     * @return array<string, array{0: FilterInput, 1: bool}>
     */
    public function getFilterFields(Factory $field_factory): array
    {
        $filters = [
            'code' => [
                $field_factory->text($this->lng->txt('registration_code'))->withMaxLength(
                    ilRegistrationCode::CODE_LENGTH
                ),
                true
            ],
        ];
        $role_map = [];
        foreach ($this->rbac_review->getGlobalRoles() as $role_id) {
            if (!\in_array($role_id, [SYSTEM_ROLE_ID, ANONYMOUS_ROLE_ID], true)) {
                $role_map[$role_id] = ilObject::_lookupTitle($role_id);
            }
        }

        $options = ['' => $this->lng->txt('registration_roles_all')] + $role_map;
        $filters['role'] = [$field_factory->select($this->lng->txt('role'), $options), true];

        $options = [
            '' => $this->lng->txt('registration_codes_access_limitation_all'),
            'unlimited' => $this->lng->txt('reg_access_limitation_none'),
            'absolute' => $this->lng->txt('reg_access_limitation_mode_absolute'),
            'relative' => $this->lng->txt('reg_access_limitation_mode_relative')
        ];
        $filters['access_limitation'] = [$field_factory->select($this->lng->txt('reg_access_limitations'), $options), true];

        $options = ['' => $this->lng->txt('registration_generated_all')];
        foreach ($this->code_repository->getGenerationDates() as $date) {
            $options[$date] = ilDatePresentation::formatDate(new ilDateTime($date, IL_CAL_UNIX));
        }
        $filters['generated'] = [$field_factory->select($this->lng->txt('registration_generated'), $options), true];

        return $filters;
    }
}
