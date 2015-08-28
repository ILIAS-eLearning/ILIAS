<?php

/**
 * Class ilDataCollectionTextField
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
class ilDataCollectionTextField extends ilDataCollectionRecordField
{

    /**
     * @param $form
     */
    public function fillFormInput(&$form)
    {
        $value = $this->getValue();
        $properties = $this->getField()->getProperties();

        if ($properties[ilDataCollectionField::PROPERTYID_TEXTAREA]) {
            $breaks = array( "<br />" );
            $input = str_ireplace($breaks, "", $value);
        } elseif ($properties[ilDataCollectionField::PROPERTYID_URL] && $json = json_decode($value)) {
            $input = $json->link;
            $input_title = $json->title;
            $form->getItemByPostVar('field_' . $this->field->getId() . '_title')->setValue($input_title);
        } else {
            $input = $value;
        }
        $form->getItemByPostVar('field_' . $this->field->getId())->setValue($input);
    }
}