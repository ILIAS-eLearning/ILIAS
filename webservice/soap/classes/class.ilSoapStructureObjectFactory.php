<?php

class ilSoapStructureObjectFactory {
	function getInstanceForObject ($object)
	{
		$classname = ilSoapStructureObjectFactory::_getClassnameForType ($object->getType());

		switch ($object->getType())
		{
			case "lm":
				return new $classname(
					$object->getId(), $object->getType(), $object->getTitle(),
					$object->getLongDescription(), $object->getRefId());
            case "glo":
				return new $classname(
					$object->getId(), $object->getType(), $object->getTitle(),
					$object->getLongDescription(), $object->getRefId());
		}
		die ("Object type ".$object->getType()." not supported");
	}

	function getInstance ($objId, $type, $title)
	{
		$classname = ilSoapStructureObjectFactory::_getClassnameForType ($type);
		return new $classname($objId, $type, $title);
	}

	function _getClassnameForType ($type)
	{
		switch ($type)
		{
			case "lm":
				include_once "./webservice/soap/classes/class.ilSoapRepositoryStructureObject.php";
				return "ilSoapRepositoryStructureObject";
			case "st":
				include_once "./webservice/soap/classes/class.ilSoapLMChapterStructureObject.php";
				return "ilSoapLMChapterStructureObject";
			case "pg":
				include_once "./webservice/soap/classes/class.ilSoapLMPageStructureObject.php";
				return "ilSoapLMPageStructureObject";
			case "glo":
			    include_once "./webservice/soap/classes/class.ilSoapRepositoryStructureObject.php";
				return "ilSoapRepositoryStructureObject";
			case "qit":
			    include_once "./webservice/soap/classes/class.ilSoapGLOTermStructureObject.php";
				return "ilSoapGLOTermStructureObject";


		}

	}
}

?>