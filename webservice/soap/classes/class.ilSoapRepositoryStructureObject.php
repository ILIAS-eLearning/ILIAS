<?php

  /*
   +-----------------------------------------------------------------------------+
   | ILIAS open source                                                           |
   +-----------------------------------------------------------------------------+
   | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
   |                                                                             |
   | This program is free software; you can redistribute it and/or               |
   | modify it under the terms of the GNU General Public License                 |
   | as published by the Free Software Foundation; either version 2              |
   | of the License, or (at your option) any later version.                      |
   |                                                                             |
   | This program is distributed in the hope that it will be useful,             |
   | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
   | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
   | GNU General Public License for more details.                                |
   |                                                                             |
   | You should have received a copy of the GNU General Public License           |
   | along with this program; if not, write to the Free Software                 |
   | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
   +-----------------------------------------------------------------------------+
  */


 /**
   * class representing a repository object as structure object
   *
   * @author Roland Kuestermann (rku@aifb.uni-karlsruhe.de)
   * @version $Id: class.ilSoapStructureReader.php,v 1.5 2006/05/23 23:09:06 hschottm Exp $
   *
   * @package ilias
   */


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
		$attrs = array(	'type' => $this->getType(),
				'obj_id' => $this->getObjId(),
				'ref_id' => $this->getRefId());

		return $attrs;

	}

	function _getTagName () {
		return "RepositoryObject";
	}


}

?>