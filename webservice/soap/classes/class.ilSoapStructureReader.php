<?php

include_once './webservice/soap/classes/class.ilSoapStructureObject.php';

class ilSoapStructureReader {
	var $object;
	var $structureObject;
	
	function ilSoapStructureReader (& $object) 
	{
		$this->object = & $object;
		$this->structureObject = & ilSoapStructureObjectFactory::getInstanceForObject ($object);
	}
	
	function getStructureObject() {
		$this->_parseStructure();		
		return $this->structureObject;
	}
	
	function _parseStructure () {
		die ("abstract");
	}
}

?>