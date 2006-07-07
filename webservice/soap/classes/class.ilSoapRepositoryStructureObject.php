<?php

include_once "./webservice/soap/classes/class.ilSoapStructureObject.php";

class ilSoapRepositoryStructureObject extends ilSoapStructureObject {
	var $ref_id;

	function ilSoapRepositoryStructureObject ($objId, $type, $title, $description, $refId) {
		parent::ilSoapStructureObject($objId, $type, $title, $description);
		$this->setRefId ($refId);
	}

		/**
	*	set current refId
	*
	*/
	function setRefId ($value) {
		$this->ref_id= $value;
	}


	/**
	*	return current ref id
	*
	*/
	function getRefId()
	{
		return $this->ref_id;
	}

	function getInternalLink () {
		return "[iln ".$this->getType()."=\"".$this->getRefId()."\"]".$this->getTitle()."[/iln]";
	}

	function getGotoLink (){
	    return ILIAS_HTTP_PATH."/". "goto.php?target=".$this->getType()."_".$this->getRefId()."&client_id=".CLIENT_ID;
	}

	function _getXMLAttributes () {
		return array(	'type' => $this->getType(),
					   	'obj_id' => $this->getObjId(),
					   	'ref_id' => $this->getRefId()
		);
	}

	function _getTagName () {
		return "RepositoryObject";
	}


}

?>