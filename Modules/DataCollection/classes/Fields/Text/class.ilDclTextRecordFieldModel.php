<?php

/**
 * Class ilDclTextRecordFieldModel
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDclTextRecordFieldModel extends ilDclBaseRecordFieldModel
{
    /**
     * @param $form ilPropertyFormGUI
     */
    public function setValueFromForm($form) {
        if ($this->getField()->hasProperty(ilDclBaseFieldModel::PROP_URL)) {
            $value = json_encode(array(
                "link" => $form->getInput("field_" . $this->getField()->getId()),
                "title" => $form->getInput("field_" . $this->getField()->getId() . '_title')));
        } else {
            $value = $form->getInput("field_" . $this->getField()->getId());
        }
        $this->setValue($value);
    }

    /**
     * @param $worksheet
     * @param $row
     * @param $col
     */
    public function fillExcelExport(ilExcel $worksheet, &$row, &$col) {
        $value = $this->getExportValue();

        if ($this->getField()->getProperty(ilDclBaseFieldModel::PROP_URL)) {
            if (is_array($value)) {
                $worksheet->setCell($row, $col, $value['link']);
                $col++;
                $worksheet->setCell($row, $col, $value['title']);
                $col++;
            } else {
                $worksheet->setCell($row, $col, $value);
                $col+= 2;
            }
        } else {
            $worksheet->setCell($row, $col, $value);
            $col++;
        }
    }

    /**
     * @return mixed|string
     */
    public function getExportValue() {
        $value = $this->getValue();

        // TODO: Handle line-breaks for excel
        if (is_array($value) && !$this->getField()->getProperty(ilDclBaseFieldModel::PROP_URL)) {
            return $value['link'];
        } else {
            return $value;
        }
        return $value;
    }

    public function getValueFromExcel($excel, $row, $col) {
        $value = parent::getValueFromExcel($excel, $row, $col);
        if ($this->getField()->hasProperty(ilDclBaseFieldModel::PROP_URL)) {
            $title = '';
            if ($excel->getCell(1, $col+1) == $this->getField()->getTitle().'_title') {
                $title = $excel->getCell($row, $col + 1);
            }
            $value = json_encode(array('link' => $value, 'title' => $title));
        }
        return $value;
    }

    public function parseValue($value) {
        if ($this->getField()->getProperty(ilDclBaseFieldModel::PROP_TEXTAREA)) {
            $return = nl2br($value);
        } else {
            $return = $value;
        }
        return $return;
    }

    /**
     * Returns sortable value for the specific field-types
     *
     * @param                           $value
     * @param ilDclBaseRecordFieldModel $record_field
     * @param bool|true                 $link
     *
     * @return int|string
     */
    public function parseSortingValue($value, $link = true) {
        if ($this->getField()->getProperty(ilDclBaseFieldModel::PROP_URL)) {
            if (is_array($value)) {
                return isset($value['title']) ? $value['title'] : $value['link'];
            } else {
                return $value;
            }
        } else {
            return $value;
        }
    }
}