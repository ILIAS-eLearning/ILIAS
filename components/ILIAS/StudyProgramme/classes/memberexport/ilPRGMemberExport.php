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

use ILIAS\Data\Factory as DataFactory;

/**
 * export assignments of PRG
 */
class ilPRGMemberExport
{
    public const EXPORT_CSV = 1;
    public const EXPORT_EXCEL = 2;

    protected int $export_type;

    /**
     * @var ilExcel|ilCSVWriter
     */
    protected $writer;

    protected int $current_row = 0;

    public function __construct(
        protected int $prg_ref_id,
        protected int $prg_obj_id,
        protected ilStudyProgrammeUserTable $prg_user_table,
        protected ilUserFormSettings $settings,
        protected ilLanguage $lng,
        protected DataFactory $data_factory
    ) {
    }

    public function create(int $type, string $filename)
    {
        switch ($type) {
            case self::EXPORT_CSV:
                $this->writer = $this->createCSV();
                $this->write();
                break;
            case self::EXPORT_EXCEL:
                $this->writer = $this->createExcel();
                $this->write();
                $this->writer->writeToFile($filename);
                break;
            default:
                throw new Exception('Unknown type for export' . $this->export_type);
        }
    }

    protected function createExcel(): ilExcel
    {
        $writer = new ilExcel();
        $writer->addSheet($this->lng->txt("assignments"));
        return $writer;
    }

    protected function createCSV(): ilCSVWriter
    {
        return new ilCSVWriter();
    }

    public function getCSVString(): ?string
    {
        if ($this->writer instanceof ilCSVWriter) {
            return $this->writer->getCSVString();
        }
        return null;
    }

    protected function write(): void
    {
        foreach ($this->prg_user_table->getColumns($this->prg_obj_id, true) as $user_table_field) {
            [$f_id, $f_title] = $user_table_field;
            if ($this->settings->enabled($f_id)) {
                $fields[$f_id] = $f_title;
            }
        }
        $this->writeRow(array_values($fields));

        $order = $this->data_factory->order('login', 'ASC');
        $data = $this->prg_user_table->fetchData($this->prg_obj_id, null, $order);
        $this->writeData(array_keys($fields), $data);
    }

    /**
     * @param string[] $fields
     * @param ilStudyProgrammeUserTableRow[] $data
     */
    protected function writeData(array $fields, array $data): void
    {
        foreach ($data as $usr_row) {
            $row = [];
            //$usr_data = ilObjUser::_lookupFields($usr_row->getUsrId());

            foreach ($fields as $f_id) {
                switch ($f_id) {
                    case 'name':
                        $row[] = $usr_row->getFirstname() . $usr_row->getLastname();
                        break;
                    case 'login':
                        $row[] = $usr_row->getLogin();
                        break;
                    case 'prg_orgus':
                        $row[] = $usr_row->getOrgUs();
                        break;
                    case 'prg_status':
                        $row[] = $usr_row->getStatus();
                        break;
                    case 'prg_completion_date':
                        $row[] = $usr_row->getCompletionDate();
                        break;
                    case 'prg_completion_by':
                        $row[] = $usr_row->getCompletionBy();
                        break;
                    case 'points':
                        $row[] = $usr_row->getPointsReachable();
                        break;
                    case 'points_required':
                        $row[] = $usr_row->getPointsRequired();
                        break;
                    case 'points_current':
                        $row[] = $usr_row->getPointsCurrent();
                        break;
                    case 'prg_custom_plan':
                        $row[] = $usr_row->getCustomPlan();
                        break;
                    case 'prg_belongs_to':
                        $row[] = $usr_row->getBelongsTo();
                        break;
                    case 'prg_assign_date':
                        $row[] = $usr_row->getAssignmentDate();
                        break;
                    case 'prg_assigned_by':
                        $row[] = $usr_row->getAssignmentBy();
                        break;
                    case 'prg_deadline':
                        $row[] = $usr_row->getDeadline();
                        break;
                    case 'prg_expiry_date':
                        $row[] = $usr_row->getExpiryDate();
                        break;
                    case 'prg_validity':
                        $row[] = $usr_row->getValidity();
                        break;
                    case 'active':
                        $row[] = $usr_row->getUserActive();
                        break;
                    default:
                        $row[] = $usr_row->getUserData($f_id);
                }
            }

            $this->writeRow($row);
        }
    }

    /**
     * @param string[] $values
     */
    protected function writeRow(array $values)
    {
        if ($this->writer instanceof ilCSVWriter) {
            $this->writeCSVRow($values);
        }
        if ($this->writer instanceof ilExcel) {
            $this->writeExcelRow($values);
        }
    }

    protected function writeCSVRow(array $values)
    {
        foreach ($values as $val) {
            $this->writer->addColumn($val);
        }
        $this->writer->addRow();
    }

    protected function writeExcelRow(array $values)
    {
        $this->current_row++;
        $current_col = 0;
        foreach ($values as $val) {
            $this->writer->setCell($this->current_row, $current_col, $val);
            $current_col++;
        }
    }
}
