<?php
/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

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
	
	function ilPageLayout($a_id=null)
	{
		global $ilias, $ilDB;
		//create new instance
		if ($a_id == null) {
			$this->layout_id = $ilDB->nextId("page_layout");
			$ilDB->insert("page_layout", array(
				"layout_id" => array("integer", $this->layout_id),
				"active" => array("integer", 0),
				"title" => array("text", ""),
				"content" => array("clob", ""),
				"description" => array("text", "")
				));
			//$query = "INSERT INTO page_layout(active) values (0);";
			//$result = $ilDB->query($query);
			//$query = "SELECT LAST_INSERT_ID() as id";
			//$res = $ilDB->query($query);
			//$row = $res->fetchRow(DB_FETCHMODE_OBJECT);
			//$this->layout_id = $row->id;
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

	/**
	 * Set style id
	 */
	public function setStyleId($a_val)
	{
		$this->style_id = $a_val;
	}

	/**
	 * Get style id
	 */
	public function getStyleId()
	{
		return $this->style_id;
	}

	/**
	 * Set special page
	 */
	public function setSpecialPage($a_val)
	{
		$this->special_page = $a_val;
	}

	/**
	 * Get special page
	 */
	public function getSpecialPage()
	{
		return $this->special_page;
	}

	/**
	 * (De-)Activate layout
	 *
	 * @param boolean $a_setting true/false
	 */
	public function activate($a_setting=true)
	{
		global $ilias, $ilDB;

		$query = "UPDATE page_layout SET active=".$ilDB->quote($a_setting, "integer").
			" WHERE layout_id =".$ilDB->quote($this->layout_id, "integer");
		$result = $ilDB->manipulate($query);
	}

	/**
	 * Delete page layout
	 */
	public function delete()
	{
		global $ilias, $ilDB;

		$query = "DELETE FROM page_layout WHERE layout_id =".$ilDB->quote($this->layout_id, "integer");
		$result = $ilDB->manipulate($query);
	}

	/**
	 * Update page layout
	 */
	public function update()
	{
		global $ilias, $ilDB;

		$query = "UPDATE page_layout SET title=".$ilDB->quote($this->title, "text").
			",description =".$ilDB->quote($this->description, "text").
			",active =".$ilDB->quote($this->active, "integer").
			",style_id =".$ilDB->quote($this->getStyleId(), "integer").
			",special_page =".$ilDB->quote((int) $this->getSpecialPage(), "integer").
			" WHERE layout_id =".$ilDB->quote($this->layout_id, "integer");
	
		$result = $ilDB->manipulate($query);
	}

	/**
	 * Read page layout
	 */
	public function readObject()
	{
		global $ilias, $ilDB;
		$query = "SELECT * FROM page_layout WHERE layout_id =".$ilDB->quote($this->layout_id, "integer");
		$result = $ilDB->query($query);
		$row = $ilDB->fetchAssoc($result);
		$this->title = $row['title'];
		$this->setStyleId($row['style_id']);
		$this->setSpecialPage($row['special_page']);
		$this->description=$row['description'];
		$this->active=$row['active'];
	}

	/**
	 * Get xml content
	 * 
	 * @return string content xml
	 */
	public function getXMLContent()
	{
		global $ilias, $ilDB;

        $r = $ilias->db->query("SELECT content FROM page_object WHERE parent_type='stys' AND page_id=".
			$ilDB->quote($this->layout_id));
	    $row = $r->fetchRow(DB_FETCHMODE_ASSOC);

		return $row['content'];
	}
	

	/**
	 * Get preview
	 */
	public function getPreview()
	{
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