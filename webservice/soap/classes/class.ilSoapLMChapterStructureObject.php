<?php

include_once "./webservice/soap/classes/class.ilSoapStructureObject.php";


class ilSoapLMChapterStructureObject extends ilSoapStructureObject{

	function ilSoapLMChapterStructureObject($objId, $type, $title) {
		parent::ilSoapStructureObject($objId, $type, $title, "");
	}


	function getInternalLink () {
		return "[iln chap=\"".$this->getObjId()."\"]".$this->getTitle()."[/iln]";
	}

}

?>