<?php

include_once "./webservice/soap/classes/class.ilSoapStructureObject.php";


class ilSoapLMPageStructureObject extends ilSoapStructureObject{
	
	function ilSoapLMPageStructureObject($objId, $type, $title) {
		parent::ilSoapStructureObject($objId, $type, $title, "");
	}
	
	
	function getInternalLink () {
		return "[iln page=\"".$this->getObjId()."\"]".$this->getTitle()."[/iln]"; 
	}
	
	function getGotoLink (){
		return "http://ilias.aifb.uni-karlsruhe.de/ilias/goto.php?target=pg_".$this->getObjId();
	}
		
	
}

?>
