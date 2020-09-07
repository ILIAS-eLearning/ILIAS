<?php
require_once('./Services/ActiveRecord/Views/class.arViewField.php');

/**
 * GUI-Class arEditField
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.7
 *
 */
class arEditField extends arViewField
{

    /**
     * @var ilPropertyFormGUI
     */
    protected $form_element = null;
    /**
     * @var arEditField
     */
    protected $subelement_of = null;


    /**
     * @param \arEditField $is_subelement_of
     */
    public function setSubelementOf($is_subelement_of)
    {
        $this->subelement_of = $is_subelement_of;
    }


    /**
     * @return \arEditField
     */
    public function getSubelementOf()
    {
        return $this->subelement_of;
    }


    /**
     * @param \ilPropertyFormGUI $form_element
     */
    public function setFormElement($form_element)
    {
        $this->form_element = $form_element;
    }


    /**
     * @return \ilPropertyFormGUI
     */
    public function getFormElement()
    {
        return $this->form_element;
    }


    /**
     * @param boolean $is_created_by_field
     */
    public function setIsCreatedByField($is_created_by_field)
    {
        if ($is_created_by_field) {
            $this->setVisible(false);
        }
        $this->is_created_by_field = $is_created_by_field;
    }


    /**
     * @param boolean $is_modified_by_field
     */
    public function setIsModifiedByField($is_modified_by_field)
    {
        if ($is_modified_by_field) {
            $this->setVisible(false);
        }
        $this->is_modified_by_field = $is_modified_by_field;
    }


    /**
     * @param $is_modification_date_field
     */
    public function setIsModificationDateField($is_modification_date_field)
    {
        if ($is_modification_date_field) {
            $this->setVisible(false);
        }
        $this->is_modification_date_field = $is_modification_date_field;
    }


    /**
     * @param $is_creation_date_field
     */
    public function setIsCreationDateField($is_creation_date_field)
    {
        if ($is_creation_date_field) {
            $this->setVisible(false);
        }
        $this->is_creation_date_field = $is_creation_date_field;
    }
}
