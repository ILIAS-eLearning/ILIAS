<?php

include_once "Services/ADT/classes/_Example/class.ilADTBasedObjectGUI.php";

/**
 * This is the GUI for the ADT-based example object 
 * 
 * It expects an existing record with Id 1 and doesn't do much
 */
class ilADTTestGUI extends ilADTBasedObjectGUI
{	
	protected function initObject()
	{
		include_once "Services/ADT/classes/_Example/class.ilADTTest.php";
		
		/*
		$test = new ilADTTest();
		$test->getName()->setText("2. Satz");
		$test->getLang()->setSelection("en");
		$home = $test->getHome();
		$home->setLatitude(1);
		$home->setLongitude(2);
		$test->getLastLogin()->setDate(new ilDateTime(time(), IL_CAL_UNIX));
		if(!$test->isValid())
		{
			var_dump($test->getAllTranslatedErrors());
		}
		else if(!$test->create())
		{			
			var_dump($test->getAllTranslatedErrors());
		}
		*/
				
		return new ilADTTest(1);
	}

	protected function prepareFormElements(ilADTGroupFormBridge $a_adt_form)
	{		
		global $lng;
		
		// :TODO:
		$a_adt_form->getForm()->setTitle($lng->txt("test_form_title"));
		$a_adt_form->setTitle($lng->txt("test_form_section_title"));
		$a_adt_form->setInfo($lng->txt("test_form_section_title_info"));
		
		foreach($a_adt_form->getElements() as $name => $element)
		{
			$element->setTitle($lng->txt("test_form_".$name));			
		}
		
		$a_adt_form->getElement("name")->setRequired(true);
		$a_adt_form->getElement("lang")->setRequired(true);
		$a_adt_form->getElement("tags")->setRequired(true);
		// $a_adt_form->getElement("last_login")->setRequired(true);
		
		$a_adt_form->getElement("lang")->forceRadio(true, array("en"=>$lng->txt("lang_en_info")));
		
		$a_adt_form->getElement("entry_date")->setParentElement("active");
		// $a_adt_form->getElement("entry_date")->setDisabled(true);
		
		// $a_adt_form->getElement("last_login")->setParentElement(array("interests", ilADTTest::INTERESTS_LANGUAGES));
		
		$a_adt_form->getElement("tags")->setParentElement(array("lang", "de"));
		$a_adt_form->getElement("tags")->setInfo($lng->txt("test_form_tags_info"));	
	}
}

?>