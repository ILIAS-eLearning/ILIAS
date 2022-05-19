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

namespace ILIAS\COPage;

/**
 * Collects all js/css/onload resources necessary for page rendering
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ResourcesCollector
{
    protected string $output_mode = "";
    protected array $js_files = [];
    protected array $css_files = [];
    protected array $onload_code = [];

    /**
     * Constructor, currently has a dependency to
     * ilPageObject due to historic reasons, this should
     * be removed in the future
     */
    public function __construct(
        string $output_mode,
        \ilPageObject $pg = null
    ) {
        // workaround (note that pcquestion currently checks for page config, if self assessment is enabled
        if (is_null($pg)) {
            $pg = new \ilLMPage();
            $pg->setXMLContent("<PageObject></PageObject>");
        }
        $this->output_mode = $output_mode;
        $this->init($pg);
    }

    protected function init(\ilPageObject $pg) : void
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

    public function getJavascriptFiles() : array
    {
        return $this->js_files;
    }

    public function getCssFiles() : array
    {
        return $this->css_files;
    }

    public function getOnloadCode() : array
    {
        return $this->onload_code;
    }
}
