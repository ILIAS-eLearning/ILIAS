<?php

/**
 * Class ilDataCollectionTextField
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDataCollectionTextField extends ilDataCollectionRecordField
{

    /**
     * @param $form ilPropertyFormGUI
     */
    public function fillFormInput(&$form)
    {
        $value = $this->getValue();
        $input = $value;

        if ($this->hasProperty(ilDataCollectionField::PROPERTYID_URL) && $json = json_decode($value)) {
            $input = $json->link;
            $input_title = $json->title;
            $form->getItemByPostVar('field_' . $this->field->getId() . '_title')->setValue($input_title);
        }

        if ($this->hasProperty(ilDataCollectionField::PROPERTYID_TEXTAREA)) {
            $breaks = array( "<br />" );
            $input = str_ireplace($breaks, "", $input);
        }

        $form->getItemByPostVar('field_' . $this->field->getId())->setValue($input);
    }

    /**
     * @param $form ilPropertyFormGUI
     */
    public function setValueFromForm(&$form) {
        if ($this->hasProperty(ilDataCollectionField::PROPERTYID_URL)) {
            $value = json_encode(array(
                "link" => $form->getInput("field_" . $this->field->getId()),
                "title" => $form->getInput("field_" . $this->field->getId() . '_title')));
        } else {
            $value = $form->getInput("field_" . $this->field->getId());
        }
        $this->setValue($value);
    }

    /**
     * @param $worksheet
     * @param $row
     * @param $col
     */
    public function fillExcelExport($worksheet, &$row, &$col) {
        $value = $this->getExportValue();
        if ($this->hasProperty(ilDataCollectionField::PROPERTYID_URL)) {
            if ($value instanceof stdClass) {
                $worksheet->writeString($row, $col, $value->link);
                $col++;
                $worksheet->writeString($row, $col, $value->title);
                $col++;
            } else {
                $worksheet->writeString($row, $col, $value);
                $col++;
                $col++;
            }
        } else {
            $worksheet->writeString($row, $col, $value);
            $col++;
        }
    }

    /**
     * @return mixed|string
     */
    public function getExportValue() {
        if (json_decode($this->getValue()) instanceof stdClass) {
            $json = json_decode($this->getValue());
            return $json->link . ($json->title ? " (".$json->title.")" : "");
        } else {
            return $this->getValue();
        }
    }

    public function getValueFromExcel($excel, $row, $col) {
        $value = $excel->val($row, $col);
        if ($this->hasProperty(ilDataCollectionField::PROPERTYID_URL)) {
            $title = '';
            if ($excel->val(1, $col+1) == $this->field->getTitle().'_title') {
                $title = $excel->val($row, $col + 1);
            }
            $value = json_encode(array('link' => $value, 'title' => $title));
        }
        return $value;
    }

    /**
     * @param $prop_id
     * @return mixed
     */
    protected function hasProperty($prop_id) {
        $properties = $this->getField()->getProperties();
        return $properties[$prop_id];
    }
}