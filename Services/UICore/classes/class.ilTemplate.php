<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UICore/lib/html-it/IT.php");
include_once("./Services/UICore/lib/html-it/ITX.php");

/**
* special template class to simplify handling of ITX/PEAR
* @author	Stefan Kesseler <skesseler@databay.de>
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
*/
class ilTemplate extends HTML_Template_ITX
{
    /**
    * variablen die immer in jedem block ersetzt werden sollen
    * @var	array
    */
    public $vars;

    /**
    * Aktueller Block
    * Der wird gemerkt bei der berladenen Funktion setCurrentBlock, damit beim ParseBlock
    * vorher ein replace auf alle Variablen gemacht werden kann, die mit dem BLockname anfangen.
    * @var	string
    */
    public $activeBlock;
    
    /**
     * @var array
     */
    protected static $il_cache = array();

    /**
     * @var bool
     */
    protected $il_use_cache;

    /**
     * @var string
     */
    protected $il_cur_key;

    /**
     * @var string
     */
    protected $tplName;

    /**
     * @var string
     */
    protected $tplPath;

    /**
     * @var string
     */
    protected $tplIdentifier;

    /**
     * constructor
     * ilTemplate constructor.
     * @param string $file template file
     * @param bool $flag1 remove unknown variables
     * @param bool $flag2 remove empty blocks
     * @param string $in_module module/service subdirectory
     * @param string $vars variables to replace
     * @param bool $plugin plugin template
     * @param bool $a_use_cache us cache
     * @throws ilTemplateException
     */
    public function __construct(
        string $file,
        bool $flag1,
        bool $flag2,
        string $in_module = "",
        string $vars = "DEFAULT",
        bool $plugin = false,
        bool $a_use_cache = true
    ) {
        $this->activeBlock = "__global__";
        $this->vars = array();
        
        $this->il_use_cache = $a_use_cache;
        $this->il_cur_key = $file . "/" . $in_module;

        $fname = $this->getTemplatePath($file, $in_module, $plugin);

        $this->tplName = basename($fname);
        $this->tplPath = dirname($fname);
        $this->tplIdentifier = $this->getTemplateIdentifier($file, $in_module);
        
        if (!file_exists($fname)) {
            throw new \LogicException("Template '$fname' was not found.");
        }

        parent::__construct();
        $this->loadTemplatefile($fname, $flag1, $flag2);
        //add tplPath to replacevars
        $this->vars["TPLPATH"] = $this->tplPath;

        // Option for baseclass HTML_Template_IT
        $this->setOption('use_preg', false);
        
        return true;
    }

    // overwrite their init function
    protected function init()
    {
        $this->free();
        $this->buildFunctionlist();
        
        $cache_hit = false;
        if ($this->il_use_cache) {
            // cache hit
            if (isset(self::$il_cache[$this->il_cur_key]) && is_array(self::$il_cache[$this->il_cur_key])) {
                $cache_hit = true;
                //echo "cache hit";
                $this->err = self::$il_cache[$this->il_cur_key]["err"];
                $this->flagBlocktrouble = self::$il_cache[$this->il_cur_key]["flagBlocktrouble"];
                $this->blocklist = self::$il_cache[$this->il_cur_key]["blocklist"];
                $this->blockdata = self::$il_cache[$this->il_cur_key]["blockdata"];
                $this->blockinner = self::$il_cache[$this->il_cur_key]["blockinner"];
                $this->blockparents = self::$il_cache[$this->il_cur_key]["blockparents"];
                $this->blockvariables = self::$il_cache[$this->il_cur_key]["blockvariables"];
            }
        }
        
        if (!$cache_hit) {
            $this->findBlocks($this->template);
            $this->template = '';
            $this->buildBlockvariablelist();
            if ($this->il_use_cache) {
                self::$il_cache[$this->il_cur_key]["err"] = $this->err;
                self::$il_cache[$this->il_cur_key]["flagBlocktrouble"] = $this->flagBlocktrouble;
                self::$il_cache[$this->il_cur_key]["blocklist"] = $this->blocklist;
                self::$il_cache[$this->il_cur_key]["blockdata"] = $this->blockdata;
                self::$il_cache[$this->il_cur_key]["blockinner"] = $this->blockinner;
                self::$il_cache[$this->il_cur_key]["blockparents"] = $this->blockparents;
                self::$il_cache[$this->il_cur_key]["blockvariables"] = $this->blockvariables;
            }
        }
        
        // we don't need it any more
        $this->template = '';
    } // end func init

    /**
    * check if block exists in actual template
    * @access	private
    * @param string blockname
    * @return	boolean
    */
    public function blockExists($a_blockname)
    {
        // added second evaluation to the return statement because the first one only works for the content block (Helmut Schottmüller, 2007-09-14)
        return (isset($this->blockvariables["content"][$a_blockname]) ? true : false) | (isset($this->blockvariables[$a_blockname]) ? true : false);
    }

    /**
     * @param	string
     * @return	string
     */
    public function get($part = "DEFAULT")
    {
        global $DIC;

        $html = $this->getUnmodified($part);

        // include the template output hook
        $ilPluginAdmin = $DIC["ilPluginAdmin"];
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
        foreach ($pl_names as $pl) {
            $ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
            $gui_class = $ui_plugin->getUIClassInstance();

            $resp = $gui_class->getHTML(
                "",
                "template_get",
                array("tpl_id" => $this->tplIdentifier, "tpl_obj" => $this, "html" => $html)
            );

            if ($resp["mode"] != ilUIHookPluginGUI::KEEP) {
                $html = $gui_class->modifyHTML($html, $resp);
            }
        }

        return $html;
    }

    /**
     * @param	string
     * @return	string
     */
    public function getUnmodified($part = "DEFAULT")
    {
        global $DIC;

        if ($part == "DEFAULT") {
            return parent::get();
        }
        return parent::get($part);
    }



    /**
    * Überladene Funktion, die sich hier lokal noch den aktuellen Block merkt.
    * @access	public
    * @param	string
    * @return	???
    */
    public function setCurrentBlock($part = "DEFAULT")
    {
        $this->activeBlock = $part;

        if ($part == "DEFAULT") {
            return parent::setCurrentBlock();
        } else {
            return parent::setCurrentBlock($part);
        }
    }

    /**
    * overwrites ITX::touchBlock.
    * @access	public
    * @param	string
    * @return	???
    */
    public function touchBlock($block)
    {
        $this->setCurrentBlock($block);
        $count = $this->fillVars();
        $this->parseCurrentBlock();

        if ($count == 0) {
            parent::touchBlock($block);
        }
    }

    /**
    * Überladene Funktion, die auf den aktuelle Block vorher noch ein replace ausführt
    * @access	public
    * @param	string
    * @return	string
    */
    public function parseCurrentBlock($part = "DEFAULT")
    {
        // Hier erst noch ein replace aufrufen
        if ($part != "DEFAULT") {
            $tmp = $this->activeBlock;
            $this->activeBlock = $part;
        }

        if ($part != "DEFAULT") {
            $this->activeBlock = $tmp;
        }

        $this->fillVars();

        $this->activeBlock = "__global__";

        if ($part == "DEFAULT") {
            return parent::parseCurrentBlock();
        } else {
            return parent::parseCurrentBlock($part);
        }
    }

    /**
    * overwrites ITX::addBlockFile
    * @access	public
    * @param	string
    * @param	string
    * @param	string		$tplname		template name
    * @param	boolean		$in_module		should be set to true, if template file is in module subdirectory
    * @return	boolean/string
    */
    public function addBlockFile($var, $block, $tplname, $in_module = false)
    {
        global $DIC;

        if (DEBUG) {
            echo "<br/>Template '" . $this->tplPath . "/" . $tplname . "'";
        }

        $tplfile = $this->getTemplatePath($tplname, $in_module);
        if (file_exists($tplfile) == false) {
            echo "<br/>Template '" . $tplfile . "' doesn't exist! aborting...";
            return false;
        }

        $id = $this->getTemplateIdentifier($tplname, $in_module);
        $template = $this->getFile($tplfile);

        // include the template input hook
        $ilPluginAdmin = $DIC["ilPluginAdmin"];
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
        foreach ($pl_names as $pl) {
            $ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
            $gui_class = $ui_plugin->getUIClassInstance();

            $resp = $gui_class->getHTML(
                "",
                "template_add",
                array("tpl_id" => $id, "tpl_obj" => $this, "html" => $template)
            );

            if ($resp["mode"] != ilUIHookPluginGUI::KEEP) {
                $template = $gui_class->modifyHTML($template, $resp);
            }
        }

        return $this->addBlock($var, $block, $template);
    }

    /**
    * all template vars defined in $vars will be replaced automatically
    * without setting and parsing them with setVariable & parseCurrentBlock
    * @access	private
    * @return	integer
    */
    private function fillVars()
    {
        $count = 0;
        reset($this->vars);

        foreach ($this->vars as $key => $val) {
            if (is_array($this->blockvariables[$this->activeBlock])) {
                if (array_key_exists($key, $this->blockvariables[$this->activeBlock])) {
                    $count++;

                    $this->setVariable($key, $val);
                }
            }
        }
        
        return $count;
    }

    /**
     * Reads a template file from the disk.
     *
     * overwrites IT:loadTemplateFile to include the template input hook
     *
     * @param    string      name of the template file
     * @param    bool        how to handle unknown variables.
     * @param    bool        how to handle empty blocks.
     * @access   public
     * @return   boolean    false on failure, otherwise true
     * @see      $template, setTemplate(), $removeUnknownVariables,
     *           $removeEmptyBlocks
     */
    public function loadTemplatefile(
        $filename,
        $removeUnknownVariables = true,
        $removeEmptyBlocks = true
    ) {
        global $DIC;

        // copied from IT:loadTemplateFile
        $template = '';
        if (!$this->flagCacheTemplatefile ||
            $this->lastTemplatefile != $filename
        ) {
            $template = $this->getFile($filename);
        }
        $this->lastTemplatefile = $filename;
        // copied.
        
        // new code to include the template input hook:
        $ilPluginAdmin = $DIC["ilPluginAdmin"];
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
        foreach ($pl_names as $pl) {
            $ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
            $gui_class = $ui_plugin->getUIClassInstance();
            
            $resp = $gui_class->getHTML(
                "",
                "template_load",
                array("tpl_id" => $this->tplIdentifier, "tpl_obj" => $this, "html" => $template)
            );

            if ($resp["mode"] != ilUIHookPluginGUI::KEEP) {
                $template = $gui_class->modifyHTML($template, $resp);
            }
        }
        // new.
        
        // copied from IT:loadTemplateFile
        return $template != '' ?
                $this->setTemplate(
                    $template,
                    $removeUnknownVariables,
                    $removeEmptyBlocks
                ) : false;
        // copied.
    }
    

    /**
    * builds a full template path with template and module name
    *
    * @param	string		$a_tplname		template name
    * @param	boolean		$in_module		should be set to true, if template file is in module subdirectory
    *
    * @return	string		full template path
    */
    protected function getTemplatePath($a_tplname, $a_in_module = false, $a_plugin = false)
    {
        global $DIC;

        $ilCtrl = null;
        if (isset($DIC["ilCtrl"])) {
            $ilCtrl = $DIC->ctrl();
        }
        
        $fname = "";
        
        if (strpos($a_tplname, "/") === false) {
            $module_path = "";
            
            if ($a_in_module != "") {
                $module_path = $a_in_module . "/";
            }

            // use ilStyleDefinition instead of account to get the current skin
            include_once "Services/Style/System/classes/class.ilStyleDefinition.php";
            if (ilStyleDefinition::getCurrentSkin() != "default") {
                $style = ilStyleDefinition::getCurrentStyle();

                $fname = "./Customizing/global/skin/" .
                        ilStyleDefinition::getCurrentSkin() . "/" . $style . "/" . $module_path
                        . basename($a_tplname);

                if ($fname == "" || !file_exists($fname)) {
                    $fname = "./Customizing/global/skin/" .
                            ilStyleDefinition::getCurrentSkin() . "/" . $module_path . basename($a_tplname);
                }
            }

            if ($fname == "" || !file_exists($fname)) {
                $fname = "./" . $module_path . "templates/default/" . basename($a_tplname);
            }
        } elseif (strpos($a_tplname, "src/UI") === 0) {
            if (class_exists("ilStyleDefinition") // for testing
                && ilStyleDefinition::getCurrentSkin() != "default") {
                $style = ilStyleDefinition::getCurrentStyle();
                $skin = ilStyleDefinition::getCurrentSkin();
                $base_path = "./Customizing/global/skin/";
                $ui_path = "/" . str_replace("src/UI/templates/default", "UI", $a_tplname);
                $fname = $base_path . ilStyleDefinition::getCurrentSkin() . "/" . $style . "/" . $ui_path;

                if (!file_exists($fname)) {
                    $fname = $base_path . $skin . "/" . $ui_path;
                }
            }

            if ($fname == "" || !file_exists($fname)) {
                $fname = $a_tplname;
            }
        } else {
            $fname = $a_tplname;
        }
        return $fname;
    }
    
    /**
     * get a unique template identifier
     *
     * The identifier is common for default or customized skins
     * but distincts templates of different services with the same name.
     *
     * This is used by the UI plugin hook for template input/output
     *
     * @param	string				$a_tplname		template name
     * @param	string				$in_module		Component, e.g. "Modules/Forum"
     * 			boolean				$in_module		or true, if component should be determined by ilCtrl
     *
     * @return	string				template identifier, e.g. "tpl.confirm.html"
     */
    private function getTemplateIdentifier($a_tplname, $a_in_module = false)
    {
        global $DIC;

        $ilCtrl = null;
        if (isset($DIC["ilCtrl"])) {
            $ilCtrl = $DIC->ctrl();
        }


        // if baseClass functionality is used (ilias.php):
        // get template directory from ilCtrl
        if (!empty($_GET["baseClass"]) && $a_in_module === true) {
            $a_in_module = $ilCtrl->getModuleDir();
        }

        if (strpos($a_tplname, "/") === false) {
            if ($a_in_module) {
                if ($a_in_module === true) {
                    $module_path = ILIAS_MODULE . "/";
                } else {
                    $module_path = $a_in_module . "/";
                }
            } else {
                $module_path = "";
            }
            
            return $module_path . basename($a_tplname);
        } else {
            return $a_tplname;
        }
    }

    public function variableExists($a_variablename)
    {
        return (isset($this->blockvariables["content"][$a_variablename]) ? true : false);
    }
}
