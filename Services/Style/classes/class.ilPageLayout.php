<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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
* Class ilPageLayout
*
* Class for Page Layouts
*
* @author Hendrik Holtmann <holtmann@me.com>
* @version $Id$
*
* @ingroup ServicesStyle
*/
class ilPageLayout 
{
	
	const SEQ_TEMPLATE_DIR = './Modules/Scorm2004/templates/editor/page_layouts_temp/thumbnails';
	
	var $layout_id = null;
	var $title = null;
	var $description = null;
	var $active = null;
	
	function ilPageLayout($a_id=null) {
		global $ilias, $ilDB;
		//create new instance
		if ($a_id == null) {
			$query = "INSERT INTO page_layout(active) values (0);";
			$result = $ilDB->query($query);
			$query = "SELECT LAST_INSERT_ID() as id";
			$res = $ilDB->query($query);
			$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
			$this->layout_id = $row->id;
			$this->active = false;
		}
	 	else {
			$this->layout_id = $a_id;
		}
	}
		
	public function getActive() {
		return $this->active;
	}

	public function getDescription() {
		return $this->description;
	}
		
	public function setDescription($a_description) {
		$this->description = $a_description;
	}
	
	public function getTitle() {
		return $this->title;
	}
	
	public function setTitle($a_title) {
	 	$this->title = $a_title;
	}
	
	public function getId() {
		return $this->layout_id;
	}
	
	
	public function activate($a_setting=true) {
		global $ilias, $ilDB;
		$query = "UPDATE page_layout SET active=".$ilDB->quote($a_setting)." WHERE layout_id =".$ilDB->quote($this->layout_id);
		$result = $ilDB->query($query);
	}
	
	public function delete($a_setting=true) {
		global $ilias, $ilDB;
		$query = "DELETE FROM page_layout WHERE layout_id =".$ilDB->quote($this->layout_id);
		$result = $ilDB->query($query);
	}
	
	public function update() {
		global $ilias, $ilDB;
		$query = "UPDATE page_layout SET title=".$ilDB->quote($this->title).
				  ",description =".$ilDB->quote($this->description).
				  ",active =".$ilDB->quote($this->active).
				   " WHERE layout_id =".$ilDB->quote($this->layout_id);
		$result = $ilDB->query($query);
	}
	
	public function readObject() {
		global $ilias, $ilDB;
		$query = "SELECT * FROM page_layout WHERE layout_id =".$ilDB->quote($this->layout_id);
		$result = $ilDB->query($query);
		$row = $result->fetchRow(DB_FETCHMODE_ASSOC);
		$this->title=$row['title'];
		$this->description=$row['description'];
		$this->active=$row['active'];
	}
	
	public function getXMLContent() {
		global $ilias, $ilDB;
         $r = $ilias->db->query("SELECT content FROM page_object WHERE parent_type='stys' AND page_id=".
								 $ilDB->quote($this->layout_id));
	     $row = $r->fetchRow(DB_FETCHMODE_ASSOC);
		return $row['content'];
	}
	
	
	public function getPreview() {
		return $this->generatePreview();	
	}
		
	
	private function getXSLPath() {
		return "./Services/Style/xml/layout2html.xsl";
	}
	
	private function generatePreview() {
		
		$xml = $this->getXMLContent();
		
		$dom = @domxml_open_mem($xml, DOMXML_LOAD_PARSING, $error);		
		$xpc = xpath_new_context($dom);
		$path = "////PlaceHolder";
		$res =& xpath_eval($xpc, $path);
		
		foreach ($res->nodeset as $item){
				$height = $item->get_attribute("Height");
				
				$height = eregi_replace("px","",$height);
				$height=$height/10;
				$item->set_attribute("Height",$height."px");
		}
		$xsl = file_get_contents($this->getXSLPath());
		
		$xml = $dom->dump_mem(0, "UTF-8");
			
		$args = array( '/_xml' => $xml, '/_xsl' => $xsl );
		
		$xh = xslt_create();
		$output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", NULL, $args, NULL);
		xslt_error($xh);
		xslt_free($xh);
		return $output;
	}
	
	/**
	* 
	*	Static access functions
	*/
	
	public static function getLayoutsAsArray($a_active=0){
	
		global $ilDB;
		$arr_layouts = array();
		if ($active!=0) {
			$add ="WHERE (active=1)";
		}
		$query = "SELECT * FROM page_layout $add ORDER BY title ";
		$result = $ilDB->query($query);
		while($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) 
		{
			array_push($arr_layouts,$row);
		}
		return $arr_layouts;
	
	}
	
	public static function getLayouts($a_active=false){
		global $ilDB;
		$arr_layouts = array();
		$add="";
		if ($a_active) {
			$add ="WHERE (active=1)";
		}
		$query = "SELECT layout_id FROM page_layout $add ORDER BY title ";
		$result = $ilDB->query($query);
		while($row = $result->fetchRow(DB_FETCHMODE_ASSOC)) 
		{
			array_push($arr_layouts,new ilPageLayout($row['layout_id']));
		}
		return $arr_layouts;
	}
	
	public static function activeLayouts()
	{
		return self::getLayouts(true);
	}
	
}