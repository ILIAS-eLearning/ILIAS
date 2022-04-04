<?php declare(strict_types=1);

/**
 * This is the GUI for the ADT-based example object
 * It expects an existing record with Id 1 and doesn't do much
 */
class ilADTTestGUI extends ilADTBasedObjectGUI
{
    protected function initObject() : ilADTBasedObject
    {
        return new ilADTTest(1);
    }

    protected function prepareFormElements(ilADTGroupFormBridge $a_adt_form) : void
    {
        $a_adt_form->getForm()->setTitle($this->lng->txt("test_form_title"));
        $a_adt_form->setTitle($this->lng->txt("test_form_section_title"));
        $a_adt_form->setInfo($this->lng->txt("test_form_section_title_info"));

        foreach ($a_adt_form->getElements() as $name => $element) {
            $element->setTitle($this->lng->txt("test_form_" . $name));
        }

        $a_adt_form->getElement("name")->setRequired(true);
        $a_adt_form->getElement("lang")->setRequired(true);
        $a_adt_form->getElement("tags")->setRequired(true);
        // $a_adt_form->getElement("last_login")->setRequired(true);

        $a_adt_form->getElement("lang")->forceRadio(true, array("en" => $this->lng->txt("lang_en_info")));

        $a_adt_form->getElement("entry_date")->setParentElement("active");
        // $a_adt_form->getElement("entry_date")->setDisabled(true);

        // $a_adt_form->getElement("last_login")->setParentElement(array("interests", ilADTTest::INTERESTS_LANGUAGES));

        $a_adt_form->getElement("tags")->setParentElement(array("lang", "de"));
        $a_adt_form->getElement("tags")->setInfo($this->lng->txt("test_form_tags_info"));
    }
}
