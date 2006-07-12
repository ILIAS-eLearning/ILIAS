<?php

include_once "./content/classes/class.ilGlossaryDefinition.php";
include_once "./webservice/soap/classes/class.ilSoapStructureReader.php";
include_once "./webservice/soap/classes/class.ilSoapStructureObjectFactory.php";

class ilSoapGLOStructureReader extends ilSoapStructureReader
{

	function ilSoapGLOStructureReader ($object)
	{
		parent::ilSoapStructureReader($object);
	}

	function _parseStructure () {
	    /* @var $this->object ilObjGlossary */

	    $terms = $this->object->getTermlist();

	    foreach ($terms as $term)
		{

		    $termStructureObject = ilSoapStructureObjectFactory::getInstance (
		      $term["id"], "qit", $term["term"]);

		    $this->structureObject->addStructureObject($termStructureObject);

		}


	}
}

?>