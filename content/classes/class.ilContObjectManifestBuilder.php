<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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

require_once("content/classes/class.ilObjContentObject.php");

/**
* Content Object (ILIAS native learning module / digilib book)
* Manifest export class
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @package content
*/
class ilContObjectManifestBuilder
{
	var $db;			// database object
	var $ilias;			// ilias object
	var $cont_obj;		// content object (learning module | digilib book)
	var $inst_id;		// installation id

	/**
	* Constructor
	* @access	public
	*/
	function ilContObjectManifestBuilder(&$a_cont_obj)
	{
		global $ilDB, $ilias;

		$this->cont_obj =& $a_cont_obj;

		$this->ilias =& $ilias;
		$this->db =& $ilDB;

		$this->inst_id = IL_INST_ID;

	}

	/**
	* build manifest structure
	*/
	function buildManifest()
	{
		require_once("classes/class.ilXmlWriter.php");

		$this->writer = new ilXmlWriter;

		// set xml header
		$this->writer->xmlHeader();
		
		// manifest start tag
		$attrs = array();
		$attrs["identifier"] = "il_".IL_INST_ID."_"."man".
			"_".$this->cont_obj->getId();
		$attrs["version"] = "";
		$attrs["xsi:schemaLocation"] = "http://www.imsproject.org/xsd/imscp_rootv1p1p2".
			" imscp_rootv1p1p2.xsd".
			" http://www.imsglobal.org/xsd/imsmd_rootv1p2p1".
			" imsmd_rootv1p2p1.xsd".
			" http://www.adlnet.org/xsd/adlcp_rootv1p2".
			" adlcp_rootv1p2.xsd";
		$this->writer->xmlStartTag("manifest", $attrs);

		// organizations start tag
		$attrs = array();
		$this->writer->xmlStartTag("organizations", $attrs);

		// organization start tag
		$attrs = array();
		$attrs["identifier"] =  "il_".IL_INST_ID."_".$this->cont_obj->getType().
			"_".$this->cont_obj->getId();
		$attrs["structure"] = "hierarchical"; 
		$this->writer->xmlStartTag("organization", $attrs);
		
		// title element
		$attrs = array();
		$this->writer->xmlElement("title", $attrs, $this->cont_obj->getTitle());
		
		// write item hierarchy
		$this->writeItemHierarchy();

		// organization end tag
		$this->writer->xmlEndTag("organization");
		
		// organizations end tag
		$this->writer->xmlEndTag("organizations");
		
		// resources start tag
		$attrs = array();
		$this->writer->xmlStartTag("resources", $attrs);
		
		// write resources
		$this->writeResources();

		// resources end tag
		$this->writer->xmlEndTag("resources");
		
		// manifest end tag
		$this->writer->xmlEndTag("manifest");
		
		// write manifest file
		//$this->xml->xmlDumpFile($this->export_dir."/".$this->subdir."/".$this->filename
		//	, false);
			
		// destroy writer object
		$this->writer->_XmlWriter;
	}
	
	/**
	* dump manifest file into directory
	*/
	function dump($a_target_dir)
	{
		$this->writer->xmlDumpFile($a_target_dir."/imsmanifest.xml", false);
	}
	
	/**
	* write item hierarchy
	*
	* this first version only writes one item for the whole learning module
	*/
	function writeItemHierarchy()
	{
		// start item
		$attrs = array();
		$attrs["identifier"] = "INDEX";
		$attrs["identifierref"] = "RINDEX";
		$this->writer->xmlStartTag("item", $attrs);
		
		// title element
		$attrs = array();
		$this->writer->xmlElement("title", $attrs, $this->cont_obj->getTitle());
		
		// end item
		$this->writer->xmlEndTag("item");
	}
	
	
	/**
	* write resources
	*
	* this first version only writes one resource for the whole learning module
	*/
	function writeResources()
	{
		$attrs = array();
		$attrs["identifier"] = "RINDEX";
		$attrs["type"] = "webcontent";
		$attrs["adlcp:scormtype"] = "asset";
		$attrs["href"] = "res/index.html";
		$this->writer->xmlElement("resource", $attrs, "");
	}

}

?>
