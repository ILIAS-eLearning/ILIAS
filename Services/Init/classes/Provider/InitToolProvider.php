<?php

namespace ILIAS\Init\Provider;

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ilUtil;
use ilValidatorAdapter;

/**
 * Class InitToolProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class InitToolProvider extends AbstractDynamicToolProvider
{

    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        if (!$called_contexts->current()->getAdditionalData()->get('DEVMODE')) {
            return [];
        }

        $content = $this->getDevModeContent();
        $identification = $this->identification_provider->identifier('devmode');
        $devmode_infos = $this->factory->tool($identification)
            ->withPosition(1)
            ->withTitle("DEVMODE")
            ->withSymbol($this->dic->ui()->factory()->symbol()->glyph()->settings())
            ->withContent($this->dic->ui()->factory()->legacy($content));

        return [$devmode_infos];
    }


    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main();
    }


    private function getDevModeContent() : string
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $ilDB = $DIC->database();

        $ftpl = new \ilTemplate("tpl.devmode.html", true, true, "Services/init");
        // execution time
        $t1 = explode(" ", $GLOBALS['ilGlobalStartTime']);
        $t2 = explode(" ", microtime());
        $diff = $t2[0] - $t1[0] + $t2[1] - $t1[1];

        $mem_usage = array();
        if (function_exists("memory_get_usage")) {
            $mem_usage[]
                = "Memory Usage: " . memory_get_usage() . " Bytes";
        }
        if (function_exists("xdebug_peak_memory_usage")) {
            $mem_usage[]
                = "XDebug Peak Memory Usage: " . xdebug_peak_memory_usage() . " Bytes";
        }
        $mem_usage[] = round($diff, 4) . " Seconds";

        if (sizeof($mem_usage)) {
            $ftpl->setVariable("MEMORY_USAGE", "<br>" . implode(" | ", $mem_usage));
        }

        if (!empty($_GET["do_dev_validate"]) && $ftpl->blockExists("xhtml_validation")) {
            require_once("Services/XHTMLValidator/classes/class.ilValidatorAdapter.php");
            $template2 = clone($this);
            //echo "-".ilValidatorAdapter::validate($template2->get(), $_GET["do_dev_validate"])."-";
            $ftpl->setCurrentBlock("xhtml_validation");
            $ftpl->setVariable("VALIDATION",
                ilValidatorAdapter::validate($template2->get("DEFAULT",
                    false, false, false, true), $_GET["do_dev_validate"]));
            $ftpl->parseCurrentBlock();
        }

        // controller history
        if (is_object($ilCtrl) && $ftpl->blockExists("c_entry")
            && $ftpl->blockExists("call_history")
        ) {
            $hist = $ilCtrl->getCallHistory();
            foreach ($hist as $entry) {
                $ftpl->setCurrentBlock("c_entry");
                $ftpl->setVariable("C_ENTRY", $entry["class"]);
                if (is_object($ilDB)) {
                    $file = $ilCtrl->lookupClassPath($entry["class"]);
                    $add = $entry["mode"] . " - " . $entry["cmd"];
                    if ($file != "") {
                        $add .= " - " . $file;
                    }
                    $ftpl->setVariable("C_FILE", $add);
                }
                $ftpl->parseCurrentBlock();
            }
            $ftpl->setCurrentBlock("call_history");
            $ftpl->parseCurrentBlock();

            $ftpl->setCurrentBlock("call_history");
            $ftpl->parseCurrentBlock();
        }

        // included files
        if (is_object($ilCtrl) && $ftpl->blockExists("i_entry")
            && $ftpl->blockExists("included_files")
        ) {
            $fs = get_included_files();
            $ifiles = array();
            $total = 0;
            foreach ($fs as $f) {
                $ifiles[] = array("file" => $f, "size" => filesize($f));
                $total += filesize($f);
            }
            $ifiles = ilUtil::sortArray($ifiles, "size", "desc", true);
            foreach ($ifiles as $f) {
                $ftpl->setCurrentBlock("i_entry");
                $ftpl->setVariable("I_ENTRY", $f["file"] . " (" . $f["size"] . " Bytes, " . round(100 / $total * $f["size"], 2) . "%)");
                $ftpl->parseCurrentBlock();
            }
            $ftpl->setCurrentBlock("i_entry");
            $ftpl->setVariable("I_ENTRY", "Total (" . $total . " Bytes, 100%)");
            $ftpl->parseCurrentBlock();
            $ftpl->setCurrentBlock("included_files");
            $ftpl->parseCurrentBlock();
        }

        return $ftpl->get();
    }
}
