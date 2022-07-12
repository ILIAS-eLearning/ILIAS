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
 ********************************************************************
 */

use PhpOffice\PhpSpreadsheet\Cell\DataType;

/**
 * Class ilDclTextRecordFieldModel
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDclTextRecordFieldModel extends ilDclBaseRecordFieldModel
{

    public function setValueFromForm(ilPropertyFormGUI $form) : void
    {
        if ($this->getField()->hasProperty(ilDclBaseFieldModel::PROP_URL)) {
            $value = array(
                "link" => $form->getInput("field_" . $this->getField()->getId()),
                "title" => $form->getInput("field_" . $this->getField()->getId() . '_title'),
            );
        } else {
            $value = $form->getInput("field_" . $this->getField()->getId());
        }
        $this->setValue($value);
    }

    public function fillExcelExport(ilExcel $worksheet, int &$row, int &$col) : void
    {
        $value = $this->getExportValue();

        if ($this->getField()->getProperty(ilDclBaseFieldModel::PROP_URL)) {
            if (is_array($value)) {
                $worksheet->setCell($row, $col, $value['link']);
                $col++;
                $worksheet->setCell($row, $col, $value['title']);
                $col++;
            } else {
                $worksheet->setCell($row, $col, $value);
                $col += 2;
            }
        } else {
            $worksheet->setCell($row, $col, $value, DataType::TYPE_STRING);
            $col++;
        }
    }

    public function addHiddenItemsToConfirmation(ilConfirmationGUI $confirmation) : void
    {
        if ($this->field->hasProperty(ilDclBaseFieldModel::PROP_URL)) {
            $value = $this->getValue();
            if (is_array($value)) {
                $confirmation->addHiddenItem('field_' . $this->field->getId(), $value['link']);
                $confirmation->addHiddenItem('field_' . $this->field->getId() . '_title', $value['title']);
            }

            return;
        }
        parent::addHiddenItemsToConfirmation($confirmation);
    }

    public function getPlainText() : string
    {
        $value = $this->getValue();

        if (is_array($value)) {
            if ($value['title']) {
                return $value['title'];
            }

            return $value['link'] ?? '';
        } else {
            return $value;
        }
    }

    public function getExportValue() : string
    {
        $value = $this->getValue();

        // TODO: Handle line-breaks for excel
        if (is_array($value) && !$this->getField()->getProperty(ilDclBaseFieldModel::PROP_URL)) {
            return $value['link'];
        } else {
            return $value;
        }
    }

    public function getValueFromExcel(ilExcel $excel, int $row, int $col) : string
    {
        $value = parent::getValueFromExcel($excel, $row, $col);
        if ($this->getField()->hasProperty(ilDclBaseFieldModel::PROP_URL)) {
            $title = '';
            if ($excel->getCell(1, $col + 1) == $this->getField()->getTitle() . '_title') {
                $title = $excel->getCell($row, $col + 1);
            }
            $value = array('link' => $value, 'title' => $title);
        }

        return $value;
    }

    /**
     * @param int|string $value
     */
    public function parseValue($value) : string
    {
        if ($this->getField()->getProperty(ilDclBaseFieldModel::PROP_TEXTAREA)
            && !$this->getField()->getProperty(ilDclBaseFieldModel::PROP_URL)
        ) {
            return nl2br($value);
        }

        return $value;
    }

    /**
     * Returns sortable value for the specific field-types
     * @param int|string $value
     */
    public function parseSortingValue($value, bool $link = true) : string
    {
        if ($this->getField()->getProperty(ilDclBaseFieldModel::PROP_URL)) {
            if (is_array($value)) {
                return $value['title'] ?? $value['link'];
            } else {
                return $value;
            }
        } else {
            return $value;
        }
    }
}
