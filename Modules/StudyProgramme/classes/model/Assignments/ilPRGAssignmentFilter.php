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

class ilPRGAssignmentFilter
{
    protected ilLanguage $lng;
    protected array $values = [];

    public function __construct(
        ilLanguage $lng
    ) {
        $this->lng = $lng;
    }

    public function withValues(array $values): self
    {
        $clone = clone $this;
        $clone->values = $values;
        return $clone;
    }

    public function toConditions(): array
    {
        $conditions = [];
        foreach ($this->getCleanedValues() as $field => $value) {
            switch ($field) {
                case 'status':
                    if ($value) {
                        $conditions[] = ilPRGAssignmentDBRepository::PROGRESS_FIELD_STATUS . '=' . $value;
                    }
                    break;
                case 'prg_status_hide_irrelevant':
                    $conditions[] = ilPRGAssignmentDBRepository::PROGRESS_FIELD_STATUS . '<>' . ilPRGProgress::STATUS_NOT_RELEVANT;
                    break;
                case 'name':
                    $conditions[] = '('
                        . 'memberdata.firstname LIKE \'%' . $value . '%\' OR' . PHP_EOL
                        . 'memberdata.lastname LIKE \'%' . $value . '%\' OR' . PHP_EOL
                        . 'memberdata.login LIKE \'%' . $value . '%\'' . PHP_EOL
                        . ')';
                    break;
                case 'invalidated':
                    if ((int) $value == ilStudyProgrammeUserTable::VALIDITY_OPTION_INVALID) {
                        $conditions[] = ilPRGAssignmentDBRepository::PROGRESS_FIELD_INVALIDATED . ' = 1';
                    }
                    if ((int) $value == ilStudyProgrammeUserTable::VALIDITY_OPTION_VALID) {
                        $conditions[] = ilPRGAssignmentDBRepository::PROGRESS_FIELD_INVALIDATED . ' = 0';
                        $conditions[] = '('
                            . ilPRGAssignmentDBRepository::PROGRESS_FIELD_STATUS . '=' . ilPRGProgress::STATUS_COMPLETED
                            . ' OR '
                            . ilPRGAssignmentDBRepository::PROGRESS_FIELD_STATUS . '=' . ilPRGProgress::STATUS_ACCREDITED
                        . ')';
                    }
                    break;

                case 'usr_active':
                    if ((int) $value === ilStudyProgrammeUserTable::OPTION_USR_ACTIVE) {
                        $conditions[] = 'memberdata.active = 1';
                    }
                    if ((int) $value === ilStudyProgrammeUserTable::OPTION_USR_INACTIVE) {
                        $conditions[] = 'memberdata.active = 0';
                    }
                    break;
                case 'vq_date':
                    list($from, $to) = array_values($value);
                    if ($from) {
                        $from = $from->get(IL_CAL_DATE);
                        $conditions[] = 'vq_date >= \'' . $from . ' 00:00:00\'';
                    }
                    if ($to) {
                        $to = $to->get(IL_CAL_DATE);
                        $conditions[] = 'vq_date <= \'' . $to . ' 23:59:59\'';
                    }
                    break;

                default:
                    throw new ilException("missing field in filter (to condition): " . $field, 1);
            }
        }
        return $conditions;
    }

    protected function getCleanedValues(): array
    {
        $ret = [];
        foreach ($this->getItemConfig() as list($id, $type, $options)) {
            if (array_key_exists($id, $this->values)) {
                if ($type === ilTable2GUI::FILTER_SELECT
                    && (
                        $this->values[$id] == ilStudyProgrammeUserTable::OPTION_ALL
                        || $this->values[$id] === false
                    )
                ) {
                    continue;
                }
                if ($type === ilTable2GUI::FILTER_TEXT
                    && $this->values[$id] == ''
                ) {
                    continue;
                }
                if ($type === ilTable2GUI::FILTER_CHECKBOX
                    && ($this->values[$id] == false || is_null($this->values[$id]))
                ) {
                    continue;
                }
                if ($type === ilTable2GUI::FILTER_DATE_RANGE
                    && $this->values[$id]['from'] == []
                    && $this->values[$id]['to'] == []
                ) {
                    continue;
                }
                $ret[$id] = $this->values[$id];
            }
        };
        return $ret;
    }

    /**
     * @return array <string $id, string $type, null | array $options>
     */
    public function getItemConfig(): array
    {
        $items = [];

        $items[] = [
            ilPRGAssignmentDBRepository::PROGRESS_FIELD_INVALIDATED, //invalidated
            ilTable2GUI::FILTER_SELECT,
            [
                ilStudyProgrammeUserTable::OPTION_ALL => $this->lng->txt("all"),
                ilStudyProgrammeUserTable::VALIDITY_OPTION_VALID => $this->lng->txt("prg_still_valid"),
                ilStudyProgrammeUserTable::VALIDITY_OPTION_INVALID => $this->lng->txt("prg_not_valid")
            ],
            $this->lng->txt('prg_validity')
        ];

        $items[] = [
            ilPRGAssignmentDBRepository::PROGRESS_FIELD_STATUS, //status
            ilTable2GUI::FILTER_SELECT,
            [
                ilStudyProgrammeUserTable::OPTION_ALL => $this->lng->txt("all"),
                ilPRGProgress::STATUS_IN_PROGRESS => $this->lng->txt("prg_status_in_progress"),
                ilPRGProgress::STATUS_COMPLETED => $this->lng->txt("prg_status_completed"),
                ilPRGProgress::STATUS_ACCREDITED => $this->lng->txt("prg_status_accredited"),
                ilPRGProgress::STATUS_NOT_RELEVANT => $this->lng->txt("prg_status_not_relevant"),
                ilPRGProgress::STATUS_FAILED => $this->lng->txt("prg_status_failed")
            ],
            ''
        ];

        $items[] = ['prg_status_hide_irrelevant', ilTable2GUI::FILTER_CHECKBOX, null, ''];

        $items[] = [
             'usr_active',
             ilTable2GUI::FILTER_SELECT,
             [
                ilStudyProgrammeUserTable::OPTION_ALL => $this->lng->txt("all"),
                ilStudyProgrammeUserTable::OPTION_USR_ACTIVE => $this->lng->txt("active_only"),
                ilStudyProgrammeUserTable::OPTION_USR_INACTIVE => $this->lng->txt("inactive_only")
             ],
             ''
        ];

        $items[] = ['name', ilTable2GUI::FILTER_TEXT, null, ''];

        $items[] = [
            ilPRGAssignmentDBRepository::PROGRESS_FIELD_VQ_DATE, //vq_date
            ilTable2GUI::FILTER_DATE_RANGE,
            null,
            ''
        ];

        return $items;
    }
}
