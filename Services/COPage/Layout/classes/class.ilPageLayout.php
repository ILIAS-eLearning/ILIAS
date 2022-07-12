<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Class for Page Layouts
 *
 * @author Hendrik Holtmann <holtmann@me.com>
 */
class ilPageLayout
{
    public const SEQ_TEMPLATE_DIR = './Modules/Scorm2004/templates/editor/page_layouts_temp/thumbnails';
    public const MODULE_SCORM = 1;
    public const MODULE_PORTFOLIO = 2;
    public const MODULE_LM = 3;
    protected int $special_page;
    protected int $style_id;

    protected ilDBInterface $db;
    public int $layout_id = 0;
    public string $title = "";
    public string $description = "";
    public bool $active = false;
    public array $modules = array();
    
    public function __construct(
        int $a_id = 0
    ) {
        global $DIC;

        $this->db = $DIC->database();
        $ilDB = $DIC->database();

        //create new instance
        if ($a_id == 0) {
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
        
    public function getActive() : bool
    {
        return $this->active;
    }

    public function getDescription() : string
    {
        return $this->description;
    }
        
    public function setDescription(string $a_description) : void
    {
        $this->description = $a_description;
    }
    
    public function getTitle() : string
    {
        return $this->title;
    }
    
    public function setTitle(string $a_title) : void
    {
        $this->title = $a_title;
    }
    
    public function getId() : int
    {
        return $this->layout_id;
    }


    /*
    public function setStyleId(int $a_val) : void
    {
        $this->style_id = $a_val;
    }

    public function getStyleId() : int
    {
        return $this->style_id;
    }*/


    public function setModules(array $a_values = []) : void
    {
        if ($a_values) {
            $valid = array_keys($this->getAvailableModules());
            $this->modules = array_intersect($a_values, $valid);
        } else {
            $this->modules = array();
        }
    }

    public function getModules() : array
    {
        return $this->modules;
    }

    /**
     * (De-)Activate layout
     */
    public function activate(
        bool $a_setting = true
    ) : void {
        $ilDB = $this->db;

        $query = "UPDATE page_layout SET active=" . $ilDB->quote($a_setting, "integer") .
            " WHERE layout_id =" . $ilDB->quote($this->layout_id, "integer");
        $result = $ilDB->manipulate($query);
    }

    /**
     * Delete page layout
     */
    public function delete() : void
    {
        $ilDB = $this->db;

        $query = "DELETE FROM page_layout WHERE layout_id =" . $ilDB->quote($this->layout_id, "integer");
        $result = $ilDB->manipulate($query);
    }

    /**
     * Update page layout
     */
    public function update() : void
    {
        $ilDB = $this->db;
        
        $mod_scorm = $mod_portfolio = $mod_lm = 0;
        if (in_array(self::MODULE_SCORM, $this->modules)) {
            $mod_scorm = 1;
        }
        if (in_array(self::MODULE_PORTFOLIO, $this->modules)) {
            $mod_portfolio = 1;
        }
        if (in_array(self::MODULE_LM, $this->modules)) {
            $mod_lm = 1;
        }

        $query = "UPDATE page_layout SET title=" . $ilDB->quote($this->title, "text") .
            ",description =" . $ilDB->quote($this->description, "text") .
            ",active =" . $ilDB->quote($this->active, "integer") .
            ",mod_scorm =" . $ilDB->quote($mod_scorm, "integer") .
            ",mod_portfolio =" . $ilDB->quote($mod_portfolio, "integer") .
            ",mod_lm =" . $ilDB->quote($mod_lm, "integer") .
            " WHERE layout_id =" . $ilDB->quote($this->layout_id, "integer");
    
        $result = $ilDB->manipulate($query);
    }

    public function readObject() : void
    {
        $ilDB = $this->db;
        $query = "SELECT * FROM page_layout WHERE layout_id =" . $ilDB->quote($this->layout_id, "integer");
        $result = $ilDB->query($query);
        $row = $ilDB->fetchAssoc($result);
        $this->title = (string) $row['title'];
        $this->description = (string) $row['description'];
        $this->active = (bool) $row['active'];
        
        $mods = array();
        if ($row["mod_scorm"]) {
            $mods[] = self::MODULE_SCORM;
        }
        if ($row["mod_portfolio"]) {
            $mods[] = self::MODULE_PORTFOLIO;
        }
        if ($row["mod_lm"]) {
            $mods[] = self::MODULE_LM;
        }
        $this->setModules($mods);
    }

    public function getXMLContent() : string
    {
        $layout_page = new ilPageLayoutPage($this->layout_id);
        return $layout_page->getXMLContent();
    }
    
    public function getPreview() : string
    {
        return $this->generatePreview();
    }
        
    private function getXSLPath() : string
    {
        return "./Services/COPage/Layout/xml/layout2html.xsl";
    }
    
    private function generatePreview() : string
    {
        $xml = $this->getXMLContent();
        
        $dom = domxml_open_mem($xml, DOMXML_LOAD_PARSING, $error);
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
    
    public static function getLayoutsAsArray(
        int $a_active = 0
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        $arr_layouts = array();
        $add = "";
        if ($a_active != 0) {
            $add = "WHERE (active=1)";
        }
        $query = "SELECT * FROM page_layout $add ORDER BY title ";
        $result = $ilDB->query($query);
        while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            if (ilPageObject::_exists("stys", $row["layout_id"])) {
                $arr_layouts[] = $row;
            }
        }
        return $arr_layouts;
    }
    
    public static function getLayouts(
        bool $a_active = false,
        int $a_module = 0
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();
        $arr_layouts = array();
        $add = "";
        $conc = " WHERE ";
        if ($a_active) {
            $add .= $conc . " (active = 1)";
            $conc = " AND ";
        }
        switch ($a_module) {
            case self::MODULE_SCORM:
                $add .= $conc . " mod_scorm = 1";
                break;
            
            case self::MODULE_PORTFOLIO:
                $add .= $conc . " mod_portfolio = 1";
                break;

            case self::MODULE_LM:
                $add .= $conc . " mod_lm = 1";
                break;
        }
        $query = "SELECT layout_id FROM page_layout $add ORDER BY title ";
        $result = $ilDB->query($query);
        while ($row = $result->fetchRow(ilDBConstants::FETCHMODE_ASSOC)) {
            $arr_layouts[] = new ilPageLayout($row['layout_id']);
        }

        return $arr_layouts;
    }
    
    /**
     * Get active layouts
     */
    public static function activeLayouts(
        int $a_module = 0
    ) : array {
        return self::getLayouts(true, $a_module);
    }
    
    /**
     * Import page layout
     */
    public static function import(
        string $a_filename,
        string $a_filepath
    ) : void {
        $imp = new ilImport();
        $imp->importEntity(
            $a_filepath,
            $a_filename,
            "pgtp",
            "Services/COPage"
        );
    }
    
    public static function getAvailableModules() : array
    {
        global $DIC;

        $lng = $DIC->language();
        
        return array(
            self::MODULE_PORTFOLIO => $lng->txt("style_page_layout_module_portfolio"),
            self::MODULE_LM => $lng->txt("style_page_layout_module_learning_module")
        );
    }
}
