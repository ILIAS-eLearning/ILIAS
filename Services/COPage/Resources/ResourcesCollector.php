<?php

/* Copyright (c) 1998-2020 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\COPage;

/**
 * Collects all js/css/onload resources necessary for page rendering
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ResourcesCollector
{
    /**
     * @var array
     */
    protected $js_files = [];

    /**
     * @var array
     */
    protected $css_files = [];

    /**
     * @var array
     */
    protected $onload_code = [];

    /**
     * Constructor, currently has a dependency to
     * ilPageObject due to historic reasons, this should
     * be removed in the future
     * @param string             $output_mode
     * @param \ilPageObject|null $pg
     */
    public function __construct(string $output_mode, \ilPageObject $pg = null)
    {
        // workaround (note that pcquestion currently checks for page config, if self assessment is enabled
        if (is_null($pg)) {
            $pg = new \ilLMPage();
        }
        $this->output_mode = $output_mode;
        $this->init($pg);
    }

    /**
     * init
     * @param \ilPageObject $pg
     */
    protected function init(\ilPageObject $pg)
    {
        // basic files must be copied of offline version as well
        // (for all other modes they are included automatically)
        if ($this->output_mode == \ilPageObjectGUI::OFFLINE) {
            $this->js_files[] = \iljQueryUtil::getLocaljQueryPath();
            $this->js_files[] = \iljQueryUtil::getLocaljQueryUIPath();
            $this->js_files[] = './Services/JavaScript/js/Basic.js';
        }

        $this->js_files[] = "./Services/COPage/js/ilCOPagePres.js";

        // for all page components...
        $defs = \ilCOPagePCDef::getPCDefinitions();
        foreach ($defs as $def) {
            $pc_class = $def["pc_class"];
            /** @var \ilPageContent $pc_obj */
            $pc_obj = new $pc_class($pg);

            // javascript files
            $js_files = $pc_obj->getJavascriptFiles($this->output_mode);
            foreach ($js_files as $js) {
                if (!in_array($js, $this->js_files)) {
                    $this->js_files[] = $js;
                }
            }

            // css files
            $css_files = $pc_obj->getCssFiles($this->output_mode);
            foreach ($css_files as $css) {
                if (!in_array($css, $this->css_files)) {
                    $this->css_files[] = $css;
                }
            }

            // onload code
            $onload_code = $pc_obj->getOnloadCode($this->output_mode);
            foreach ($onload_code as $code) {
                $this->onload_code[] = $code;
            }
        }
    }

    /**
     * Get javascript files
     * @return array
     */
    public function getJavascriptFiles()
    {
        return $this->js_files;
    }

    /**
     * Get css files
     * @return array
     */
    public function getCssFiles()
    {
        return $this->css_files;
    }

    /**
     * Get onload code
     * @return array
     */
    public function getOnloadCode()
    {
        return $this->onload_code;
    }
}
