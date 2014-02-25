<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Export/classes/class.ilXmlImporter.php");

/**
 * Importer class for user data
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilUserImporter extends ilXmlImporter
{

	/**
	 * Initialisation
	 */
	function init()
	{
		include_once("./Services/User/classes/class.ilUserDataSet.php");
		$this->ds = new ilUserDataSet();
		$this->ds->setDSPrefix("ds");
		$this->ds->setImportDirectory($this->getImportDirectory());
	}


	/**
	 * Import XML
	 *
	 * @param
	 * @return
	 */
	function importXmlRepresentation($a_entity, $a_id, $a_xml, $a_mapping)
	{
		include_once("./Services/DataSet/classes/class.ilDataSetImportParser.php");
		$parser = new ilDataSetImportParser($a_entity, $this->getSchemaVersion(),
			$a_xml, $this->ds, $a_mapping);
	}
	
	function finalProcessing($a_mapping)
	{				
		if(is_array($this->ds->multi))
		{
			foreach($this->ds->multi as $usr_id => $values)
			{
				$usr_obj = new ilObjUser($usr_id);
				 
				if(isset($values["interests_general"]))
				{
					$usr_obj->setGeneralInterests($values["interests_general"]);
				}
				else
				{
					$usr_obj->setGeneralInterests();
				}
				if(isset($values["interests_help_offered"]))
				{
					$usr_obj->setOfferingHelp($values["interests_help_offered"]);
				}
				else
				{
					$usr_obj->setOfferingHelp();
				}
				if(isset($values["interests_help_looking"]))
				{
					$usr_obj->setLookingForHelp($values["interests_help_looking"]);
				}
				else
				{
					$usr_obj->setLookingForHelp();
				}		

				$usr_obj->updateMultiTextFields();
			}
		}				
	}
}

?>