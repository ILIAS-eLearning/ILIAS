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

require_once("classes/class.ilSaxParser.php");

/**
* Forum Import Parser
*
* @author Stefan Meyer <smeyer@databay.de>
* @version $Id$Id: class.ilForumImportParser.php,v 1.6 2006/05/19 15:39:16 akill Exp $
*
* @ingroup ModulesForum
*/
class ilForumImportParser extends ilSaxParser
{
	var $parent;
	var $counter;

	/**
	* Constructor
	*
	* @param	string		$a_xml_file		xml file
	*
	* @access	public
	*/

	function ilForumImportParser($a_xml_file,$a_parent_id)
	{
		define('EXPORT_VERSION',4);

		parent::ilSaxParser($a_xml_file);

		// SET MEMBER VARIABLES
		$this->parent = $a_parent_id;
		$this->counter = 0;

	}


	function getParent()
	{
		return $this->parent;
	}

	function __pushParentId($a_id)
	{
		$this->parent[] = $a_id;
	}
	function __popParentId()
	{
		array_pop($this->parent);

		return true;
	}
	function __getParentId()
	{
		return $this->parent[count($this->parent) - 1];
	}
	
	function getRefId()
	{
		return $this->ref_id;
	}


	/**
	* set event handler
	* should be overwritten by inherited class
	* @access	private
	*/
	function setHandlers($a_xml_parser)
	{
		xml_set_object($a_xml_parser,$this);
		xml_set_element_handler($a_xml_parser,'handlerBeginTag','handlerEndTag');
		xml_set_character_data_handler($a_xml_parser,'handlerCharacterData');
	}

	/**
	* start the parser
	*/
	function startParsing()
	{
		// SET FIRST PARENT
		parent::startParsing();

		return true;
	}


	/**
	* handler for begin of element
	*/
	function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
	{
		global $ilErr;

		switch($a_name)
		{
			// FORUM DATA
			case "forum":
				if($a_attribs["exportVersion"] < EXPORT_VERSION)
				{
					$ilErr->raiseError("!!! This export Version isn't supported, update your ILIAS 2 installation"
									   ,$ilErr->WARNING);	
				}
				$this->__createNew($a_attribs["id"]);

				// FINALLY SET FIRST PARENT
				$this->parent = array(0);
				break;

			case "title":
			case "description":
				$this->cdata = '';
				break;

			case "moderator":
				if($mod = ilObjUser::_getImportedUserId($a_attribs["id"]))
				{
					$this->__addModerator($mod);
				}
			// THREAD DATA
			case "thread":
				// EMPTY DATA ARRAY
				$this->thread = array();
				$this->thread_start = true;
				$this->__pushParentId(0);
				#this->parent = array(0);
				break;

			case "threadAuthor":
				$this->thread["author"] = ilObjUser::_getImportedUserId($a_attribs["id"]);
				$this->thread["login"] = $a_attribs["login"];
				break;

			// POST DATA
			case "posting":
				// EMPTY DATA ARRAY
				$this->post = array();
				break;

			case "postingAuthor":
				$this->post["author"] = ilObjUser::_getImportedUserId($a_attribs["id"]);
				$this->post["login"] = $a_attribs["login"];
				break;
				
		}
	}


	/**
	* handler for end of element
	*/
	function handlerEndTag($a_xml_parser, $a_name)
	{
		switch($a_name)
		{
			// FORUM DATA
			case "title":
				$this->forum->setTitle($this->cdata);
				$this->forum->update();
				break;

			case "description":
				$this->forum->setDescription($this->cdata);
				$this->forum->update();

				// DATA COMPLETE UPDATE 'Topic_data'
				$this->__addTopic();
				break;

			// THREAD DATA 
			case "thread":
				$this->__popParentId();
				#unset($this->parent[count($this->parent)-1]);
				break;
	
			case "threadTitle":
				$this->thread["title"] = $this->cdata;
				break;

			case "threadCreationDate":
				$this->thread["c_time"] = $this->cdata;
				break;
				
			// POST DATA
			case "posting":
				$this->__popParentId();
				#unset($this->parent[count($this->parent)-1]);
				break;

			case "postingTitle":
				$this->post["title"] = $this->cdata;
				break;

			case "postingCreationDate":
				$this->post["c_time"] = $this->cdata;
				break;

			case "message":
				$this->post["message"] = $this->cdata;

				// ALL DATA COMPLETE, INSERT NODE HERE
				if($this->thread_start)
				{
					$this->__addThread();
					$this->thread_start = false;
				}
				else
				{
					$this->__addPost();
				}
				break;

			case "forum":
				break;

		}
		$this->cdata = '';
	}


	/**
	* handler for character data
	*/
	function handlerCharacterData($a_xml_parser, $a_data)
	{
		// i don't know why this is necessary, but
		// the parser seems to convert "&gt;" to ">" and "&lt;" to "<"
		// in character data, but we don't want that, because it's the
		// way we mask user html in our content, so we convert back...
		$a_data = str_replace("<","&lt;",$a_data);
		$a_data = str_replace(">","&gt;",$a_data);

		if(!empty($a_data))
		{
			$this->cdata .= $a_data;
		}
	}
	
	// PRIVATE
	function __createNew($a_id)
	{
		include_once "./Modules/Forum/classes/class.ilObjForum.php";

		$this->forum =& new ilObjForum();
		$this->forum->setImportId($a_id);
		$this->forum->setTitle("empty");
		$this->forum->create();
		$this->forum->createReference();
		//$this->forum->putInTree($this->getParent());
		$this->forum->putInTree($_GET["ref_id"]);

		// INIT DEFAULT PERMISSIONS
		$this->forum->setPermissions($this->forum->getRefId());

		// INIT DEFAULT MODERATOR ROLE
		$this->roles = $this->forum->initDefaultRoles();


		$this->ref_id = $this->forum->getRefId();

		
		return true;
	}

	function __addThread()
	{
		global $ilDB;
		
		$this->__initForumObject();

		$this->forum_obj->setImportName($this->thread["login"]);
		$this->forum_obj->setWhereCondition("top_frm_fk = ".$ilDB->quote($this->forum->getId()));
		$topic = $this->forum_obj->getOneTopic();

		// GENERATE IT AND 'INCREMENT' parent variable
/*		$this->parent[] = (int) $this->forum_obj->generateThread($topic["top_pk"],$this->thread["author"],
																 $this->thread["title"],
																 $this->post["message"],0,0,'',date("Y-m-d H:i:s",$this->thread["c_time"]));*/
		$this->__pushParentId((int) $this->forum_obj->generateThread($topic["top_pk"],$this->thread["author"],
																	 $this->thread["title"],
																	 $this->post["message"],0,0,'',date("Y-m-d H:i:s",$this->thread["c_time"])));
		
		$this->forum_obj->setDbTable("frm_data");
		$this->forum_obj->setWhereCondition("top_pk = ".$ilDB->quote($topic["top_pk"]));
		$this->forum_obj->updateVisits($topic["top_pk"]);

	}

	function __addPost()
	{
		global $ilDB;
		
		$this->forum_obj->setImportName($this->post["login"]);
		$this->forum_obj->setWhereCondition("top_frm_fk = ".$ilDB->quote($this->forum->getId()));
		$topic = $this->forum_obj->getOneTopic();
		$post = $this->forum_obj->getPostById($this->__getParentId());
		#$post = $this->forum_obj->getPostById($this->parent[count($this->parent)-1]);
		
/*		$this->parent[] = (int) $this->forum_obj->generatePost($topic["top_pk"],$post["pos_thr_fk"],$this->post["author"],
															   $this->post["message"],$this->parent[count($this->parent)-1],
															   $this->post["title"],'',date("Y-m-d H:i:s",$this->post["c_time"]));*/
		$this->__pushParentId((int) $this->forum_obj->generatePost($topic["top_pk"],$post["pos_thr_fk"],$this->post["author"],
																 $this->post["message"],$this->parent[count($this->parent)-1],
																 0,$this->post["title"],'',date("Y-m-d H:i:s",$this->post["c_time"])));

		return true;
	}

	function __initForumObject()
	{
		include_once "./Modules/Forum/classes/class.ilForum.php";

		$this->forum_obj =& new ilForum();
		$this->forum_obj->setForumRefId($this->ref_id);

		return true;
	}

	function __addTopic()
	{
		global $ilDB;

		$query = "INSERT INTO frm_data VALUES('0',".
			$ilDB->quote($this->forum->getId()).",".
			$ilDB->quote($this->forum->getTitle()).",".
			$ilDB->quote($this->forum->getDescription()).",'".
			"0','0','".
			"',".$ilDB->quote($this->roles[0]).",'".
			date("Y:m:d H:i:s")."','".
			"0','".
			date("Y:m:d H:i:s")."','".
			"0',".
			$ilDB->quote($_SESSION["AccountId"]).")";
		
		$ilDB->query($query);

		// TO ENSURE THERE IS MINIMUM ONE MODERATOR 
		$this->__addModerator($_SESSION["AccountId"]);
		return true;
	}

	function __addModerator($id)
	{
		global $rbacadmin;

		$rbacadmin->assignUser($this->roles[0],$id, "n");

		return true;
	}
}
?>