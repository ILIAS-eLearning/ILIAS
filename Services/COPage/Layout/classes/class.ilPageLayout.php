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
    /**
     * @var ilDB
     */
    protected $db;

    
    const SEQ_TEMPLATE_DIR = './Modules/Scorm2004/templates/editor/page_layouts_temp/thumbnails';
    
    const MODULE_SCORM = 1;
    const MODULE_PORTFOLIO = 2;
    
    public $layout_id = null;
    public $title = null;
    public $description = null;
    public $active = null;
    public $modules = array();
    
    public function __construct($a_id = null)
    {
        global $DIC;

        $this->db = $DIC->database();
        $ilDB = $DIC->database();
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
            //$row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT);
            //$this->layout_id = $row->id;
            $this->active = false;
        } else {
            $this->layout_id = $a_id;
        }
    }
        
    public function getActive()
    {
        return $this->active;
    }

    public function getDescription()
    {
        return $this->description;
    }
        
    public function setDescription($a_description)
    {
        $this->description = $a_description;
    }
    
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTitle($a_title)
    {
        $this->title = $a_title;
    }
    
    public function getId()
    {
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
     * Set modules
     */
    public function setModules(array $a_values = null)
    {
        if ($a_values) {
            $valid = array_keys($this->getAvailableModules());
            $this->modules = array_intersect($a_values, $valid);
        } else {
            $this->modules = array();
        }
    }

    /**
     * Get modules
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * (De-)Activate layout
     *
     * @param boolean $a_setting true/false
     */
    public function activate($a_setting = true)
    {
        $ilDB = $this->db;

        $query = "UPDATE page_layout SET active=" . $ilDB->quote($a_setting, "integer") .
            " WHERE layout_id =" . $ilDB->quote($this->layout_id, "integer");
        $result = $ilDB->manipulate($query);
    }

    /**
     * Delete page layout
     */
    public function delete()
    {
        $ilDB = $this->db;

        $query = "DELETE FROM page_layout WHERE layout_id =" . $ilDB->quote($this->layout_id, "integer");
        $result = $ilDB->manipulate($query);
    }

    /**
     * Update page layout
     */
    public function update()
    {
        $ilDB = $this->db;
        
        $mod_scorm = $mod_portfolio = 0;
        if (in_array(self::MODULE_SCORM, $this->modules)) {
            $mod_scorm = 1;
        }
        if (in_array(self::MODULE_PORTFOLIO, $this->modules)) {
            $mod_portfolio = 1;
        }

        $query = "UPDATE page_layout SET title=" . $ilDB->quote($this->title, "text") .
            ",description =" . $ilDB->quote($this->description, "text") .
            ",active =" . $ilDB->quote($this->active, "integer") .
            ",style_id =" . $ilDB->quote($this->getStyleId(), "integer") .
            ",special_page =" . $ilDB->quote((int) $this->getSpecialPage(), "integer") .
            ",mod_scorm =" . $ilDB->quote($mod_scorm, "integer") .
            ",mod_portfolio =" . $ilDB->quote($mod_portfolio, "integer") .
            " WHERE layout_id =" . $ilDB->quote($this->layout_id, "integer");
    
        $result = $ilDB->manipulate($query);
    }

    /**
     * Read page layout
     */
    public function readObject()
    {
        $ilDB = $this->db;
        $query = "SELECT * FROM page_layout WHERE layout_id =" . $ilDB->quote($this->layout_id, "integer");
        $result = $ilDB->query($query);
        $row = $ilDB->fetchAssoc($result);
        $this->title = $row['title'];
        $this->setStyleId($row['style_id']);
        $this->setSpecialPage($row['special_page']);
        $this->description = $row['description'];
        $this->active = $row['active'];
        
        $mods = array();
        if ($row["mod_scorm"]) {
            $mods[] = self::MODULE_SCORM;
        }
        if ($row["mod_portfolio"]) {
            $mods[] = self::MODULE_PORTFOLIO;
        }
        $this->setModules($mods);
    }

    /**
     * Get xml content
     *
     * @return string content xml
     */
    public function getXMLContent()
    {
        include_once "Services/COPage/Layout/classes/class.ilPageLayoutPage.php";
        $layout_page = new ilPageLayoutPage($this->layout_id);
        return $layout_page->getXMLContent();
    }
    

    /**
     * Get preview
     */
    public function getPreview()
    {
        return $this->generatePreview();
    }
        
    
    private function getXSLPath()
    {
        return "./Services/COPage/Layout/xml/layout2html.xsl";
    }
    
    private function generatePreview()
    {
        $xml = $this->getXMLContent();
        
        $dom = @domxml_open_mem($xml, DOMXML_LOAD_PARSING, $error);
        $xpc = xpath_new_context($dom);
        $path = "////PlaceHolder";
        $res = xpath_eval($xpc, $path);
        
        foreach ($res->nodeset as $item) {
            $height = $item->get_attribute("Height");
                
            $height = str_ireplace("px", "", $height);
            $height = $height / 10;
            $item->set_attribute("Height", $height . "px");
        }
        $xsl = file_get_contents($this->getXSLPath());
        
        $xml = $dom->dump_mem(0, "UTF-8");
            
        $args = array( '/_xml' => $xml, '/_xsl' => $xsl );
        
        $xh = xslt_create();
        $output = xslt_process($xh, "arg:/_xml", "arg:/_xsl", null, $args, null);
        xslt_error($xh);
        xslt_free($xh);
        return $output;
    }
    
    /**
    *
    *	Static access functions
    */
    
    public static function getLayoutsAsArray($a_active = 0)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $arr_layouts = array();
        if ($active != 0) {
            $add = "WHERE (active=1)";
        }
        $query = "SELECT * FROM page_layout $add ORDER BY title ";
        $result = $ilDB->query($query);
        while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            array_push($arr_layouts, $row);
        }
        return $arr_layouts;
    }
    
    /**
     * Get layouts
     */
    public static function getLayouts($a_active = false, $a_special_page = false, $a_module = null)
    {
        global $DIC;

        $ilDB = $DIC->database();
        $arr_layouts = array();
        $add = "WHERE special_page = " . $ilDB->quote($a_special_page, "integer");
        if ($a_active) {
            $add .= " AND (active = 1)";
        }
        switch ($a_module) {
            case self::MODULE_SCORM:
                $add .= " AND mod_scorm = 1";
                break;
            
            case self::MODULE_PORTFOLIO:
                $add .= " AND mod_portfolio = 1";
                break;
        }
        $query = "SELECT layout_id FROM page_layout $add ORDER BY title ";
        $result = $ilDB->query($query);
        while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            array_push($arr_layouts, new ilPageLayout($row['layout_id']));
        }

        return $arr_layouts;
    }
    
    /**
     * Get active layouts
     */
    public static function activeLayouts($a_special_page = false, $a_module = null)
    {
        return self::getLayouts(true, $a_special_page, $a_module);
    }
    
    /**
     * Import page layout
     *
     * @param string $a_filename file name
     * @param string $a_fiepath complete path (incl. name) to file
     *
     * @return object new object
     */
    public static function import($a_filename, $a_filepath)
    {
        include_once("./Services/Export/classes/class.ilImport.php");
        $imp = new ilImport();
        $imp->importEntity(
            $a_filepath,
            $a_filename,
            "pgtp",
            "Services/COPage"
        );
    }
    
    public static function getAvailableModules()
    {
        global $DIC;

        $lng = $DIC->language();
        
        return array(
            self::MODULE_SCORM => $lng->txt("style_page_layout_module_scorm"),
            self::MODULE_PORTFOLIO => $lng->txt("style_page_layout_module_portfolio")
        );
    }
}
