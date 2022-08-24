<?php

declare(strict_types=1);

/* Copyright (c) 1998-2022 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/../lib/html-it/IT.php';
require_once __DIR__ . '/../lib/html-it/ITX.php';

/**
 * special template class to simplify handling of ITX/PEAR
 * @author Stefan Kesseler <skesseler@databay.de>
 * @author Sascha Hofmann <shofmann@databay.de>
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilTemplate extends HTML_Template_ITX
{
    /**
     * variablen die immer in jedem block ersetzt werden sollen
     */
    public array $vars = [];

    /**
     * Aktueller Block
     * Der wird gemerkt bei der berladenen Funktion setCurrentBlock, damit beim ParseBlock
     * vorher ein replace auf alle Variablen gemacht werden kann, die mit dem BLockname anfangen.
     */
    public string $activeBlock = '';

    protected static array $il_cache = [];

    protected bool $il_use_cache;

    protected string $il_cur_key;

    protected string $tplName;

    protected string $tplPath;

    protected string $tplIdentifier;

    /**
     * constructor
     * ilTemplate constructor.
     * @param string $file        template file
     * @param bool   $flag1       remove unknown variables
     * @param bool   $flag2       remove empty blocks
     * @param string $in_module   module/service subdirectory
     * @param string $vars        variables to replace
     * @param bool   $plugin      plugin template
     * @param bool   $a_use_cache us cache
     * @throws ilTemplateException|ilSystemStyleException
     */
    public function __construct(
        string $file,
        bool $flag1,
        bool $flag2,
        string $in_module = "",
        string $vars = ilGlobalTemplateInterface::DEFAULT_BLOCK,
        bool $plugin = false,
        bool $a_use_cache = true
    ) {
        $this->activeBlock = HTML_Template_IT::IT_DEFAULT_BLOCK;
        $this->il_use_cache = $a_use_cache;
        $this->il_cur_key = $file . "/" . $in_module;

        $fname = $this->getTemplatePath($file, $in_module);
        if (!file_exists($fname)) {
            throw new ilTemplateException("Template '$fname' was not found.");
        }

        $this->tplName = basename($fname);
        $this->tplPath = dirname($fname);
        $this->vars["TPLPATH"] = $this->tplPath;
        $this->tplIdentifier = $this->getTemplateIdentifier($file, $in_module);

        parent::__construct();

        $this->loadTemplatefile($fname, $flag1, $flag2);
        $this->setOption('use_preg', false);
    }

    protected function init(): void
    {
        $this->free();
        $this->buildFunctionlist();

        $cache_hit = false;
        if ($this->il_use_cache &&
            isset(self::$il_cache[$this->il_cur_key]) &&
            is_array(self::$il_cache[$this->il_cur_key])
        ) {
            $cache_hit = true;
            $this->err = self::$il_cache[$this->il_cur_key]["err"];
            $this->flagBlocktrouble = self::$il_cache[$this->il_cur_key]["flagBlocktrouble"];
            $this->blocklist = self::$il_cache[$this->il_cur_key]["blocklist"];
            $this->blockdata = self::$il_cache[$this->il_cur_key]["blockdata"];
            $this->blockinner = self::$il_cache[$this->il_cur_key]["blockinner"];
            $this->blockparents = self::$il_cache[$this->il_cur_key]["blockparents"];
            $this->blockvariables = self::$il_cache[$this->il_cur_key]["blockvariables"];
        }

        if (!$cache_hit) {
            $this->findBlocks($this->template);
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

        // we don't need it anymore
        $this->template = '';
    }

    public function blockExists(string $a_blockname): bool
    {
        // added second evaluation to the return statement because the first one
        // only works for the content block (Helmut Schottmüller, 2007-09-14).
        return
            isset($this->blockvariables["content"][$a_blockname]) ||
            isset($this->blockvariables[$a_blockname]);
    }

    public function get(string $part = ilGlobalTemplateInterface::DEFAULT_BLOCK): string
    {
        global $DIC;

        $html = $this->getUnmodified($part);
        $component_factory = $DIC["component.factory"];
        foreach ($component_factory->getActivePluginsInSlot("uihk") as $ui_plugin) {
            $gui_class = $ui_plugin->getUIClassInstance();
            $resp = $gui_class->getHTML(
                "",
                "template_get",
                [
                    "tpl_id" => $this->tplIdentifier,
                    "tpl_obj" => $this,
                    "html" => $html
                ]
            );

            if (ilUIHookPluginGUI::KEEP !== $resp["mode"]) {
                $html = $gui_class->modifyHTML($html, $resp);
            }
        }

        return $html;
    }

    /**
     * @throws ilTemplateException
     */
    public function getUnmodified(string $part = ilGlobalTemplateInterface::DEFAULT_BLOCK): string
    {
        // I can't believe how garbage this is.
        if (ilGlobalTemplateInterface::DEFAULT_BLOCK === $part) {
            $part = self::IT_DEFAULT_BLOCK;
        }

        return parent::get($part);
    }

    /**
     * @throws ilTemplateException
     */
    public function setCurrentBlock(string $part = ilGlobalTemplateInterface::DEFAULT_BLOCK): bool
    {
        // I can't believe how garbage this is.
        if (ilGlobalTemplateInterface::DEFAULT_BLOCK === $part) {
            $part = self::IT_DEFAULT_BLOCK;
        }

        $this->activeBlock = $part;
        return parent::setCurrentBlock($part);
    }

    /**
     * @throws ilTemplateException
     */
    public function touchBlock(string $block): bool
    {
        $this->setCurrentBlock($block);
        $count = $this->fillVars();
        $this->parseCurrentBlock();

        if (0 === $count) {
            return parent::touchBlock($block);
        }

        return false;
    }

    /**
     * @throws ilTemplateException
     */
    public function parseCurrentBlock(string $part = ilGlobalTemplateInterface::DEFAULT_BLOCK): bool
    {
        $this->fillVars();
        $this->activeBlock = self::IT_DEFAULT_BLOCK;

        return parent::parseCurrentBlock();
    }

    public function addBlockFile(string $var, string $block, string $tplname, string $in_module = null): bool
    {
        global $DIC;

        if (DEBUG) {
            echo "<br/>Template '" . $this->tplPath . "/" . $tplname . "'";
        }

        $tplfile = $this->getTemplatePath($tplname, $in_module);
        if (file_exists($tplfile) === false) {
            echo "<br/>Template '" . $tplfile . "' doesn't exist! aborting...";
            return false;
        }

        $id = $this->getTemplateIdentifier($tplname, $in_module);
        $template = $this->getFile($tplfile);
        $component_factory = $DIC["component.factory"];
        foreach ($component_factory->getActivePluginsInSlot("uihk") as $ui_plugin) {
            $gui_class = $ui_plugin->getUIClassInstance();
            $resp = $gui_class->getHTML(
                "",
                "template_add",
                [
                    "tpl_id" => $id,
                    "tpl_obj" => $this,
                    "html" => $template,
                ]
            );

            if ($resp["mode"] !== ilUIHookPluginGUI::KEEP) {
                $template = $gui_class->modifyHTML($template, $resp);
            }
        }

        return $this->addBlock($var, $block, $template);
    }

    /**
     * all template vars defined in $vars will be replaced automatically
     * without setting and parsing them with setVariable & parseCurrentBlock
     */
    private function fillVars(): int
    {
        $count = 0;
        foreach ($this->vars as $key => $val) {
            if (is_array($this->blockvariables[$this->activeBlock]) &&
                array_key_exists($key, $this->blockvariables[$this->activeBlock])
            ) {
                $this->setVariable($key, $val);
                $count++;
            }
        }

        return $count;
    }

    public function loadTemplatefile(
        string $filename,
        bool $removeUnknownVariables = true,
        bool $removeEmptyBlocks = true
    ): bool {
        global $DIC;

        $template = '';
        if (!$this->flagCacheTemplatefile ||
            $this->lastTemplatefile !== $filename
        ) {
            $template = $this->getFile($filename);
        }
        $this->lastTemplatefile = $filename;

        $component_factory = $DIC["component.factory"];
        foreach ($component_factory->getActivePluginsInSlot("uihk") as $ui_plugin) {
            $gui_class = $ui_plugin->getUIClassInstance();
            $resp = $gui_class->getHTML(
                "",
                "template_load",
                [
                    "tpl_id" => $this->tplIdentifier,
                    "tpl_obj" => $this,
                    "html" => $template,
                ]
            );

            if ($resp["mode"] !== ilUIHookPluginGUI::KEEP) {
                $template = $gui_class->modifyHTML($template, $resp);
            }
        }

        return
            $template !== '' &&
            $this->setTemplate(
                $template,
                $removeUnknownVariables,
                $removeEmptyBlocks
            );
    }

    /**
     * @throws ilSystemStyleException
     */
    protected function getTemplatePath(string $a_tplname, string $a_in_module = null): string
    {
        $fname = "";
        if (strpos($a_tplname, "/") === false) {
            $module_path = "";

            if ($a_in_module !== "") {
                $module_path = $a_in_module . "/";
            }

            // use ilStyleDefinition instead of account to get the current skin
            if (ilStyleDefinition::getCurrentSkin() !== "default") {
                $style = ilStyleDefinition::getCurrentStyle();

                $fname = "./Customizing/global/skin/" .
                    ilStyleDefinition::getCurrentSkin() . "/" . $style . "/" . $module_path
                    . basename($a_tplname);

                if ($fname === "" || !file_exists($fname)) {
                    $fname = "./Customizing/global/skin/" .
                        ilStyleDefinition::getCurrentSkin() . "/" . $module_path . basename($a_tplname);
                }
            }

            if ($fname === "" || !file_exists($fname)) {
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
     * The identifier is common for default or customized skins
     * but distincts templates of different services with the same name.
     * This is used by the UI plugin hook for template input/output
     */
    public function getTemplateIdentifier(string $a_tplname, string $a_in_module = null): string
    {
        if (strpos($a_tplname, "/") === false) {
            if (null !== $a_in_module) {
                $module_path = $a_in_module . "/";
            } else {
                $module_path = "";
            }

            return $module_path . basename($a_tplname);
        }

        return $a_tplname;
    }

    public function variableExists(string $a_variablename): bool
    {
        return isset($this->blockvariables["content"][$a_variablename]);
    }
}
