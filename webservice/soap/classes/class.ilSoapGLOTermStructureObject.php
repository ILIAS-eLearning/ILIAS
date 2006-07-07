<?php

include_once "./webservice/soap/classes/class.ilSoapStructureObject.php";


class ilSoapGLOTermStructureObject extends ilSoapStructureObject{

	function ilSoapGLOTermStructureObject($objId, $type, $title) {
		parent::ilSoapStructureObject($objId, $type, $title, "");
	}


	function getInternalLink () {
		return "[iln term=\"".$this->getObjId()."\"]".$this->getTitle()."[/iln]";
	}

	function getGotoLink (){
	    global $ilInit;
	    /* @var $ilInit ilInitialisation */
		return ILIAS_HTTP_PATH."/". "goto.php?target=git_".IL_INST_ID."_".$this->getObjId()."&client_id=".CLIENT_ID;
	}


}

?>
