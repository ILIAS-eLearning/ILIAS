<?php

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Class ilLMGSToolProvider
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilRepositoryGSToolProvider extends AbstractDynamicToolProvider
{

    const SHOW_TREE_TOOL = 'show_tree_tool';


    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main()->repository();
    }


    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        global $DIC;

        /** @var ilObjectDefinition $objDefinition */
        $objDefinition = $DIC["objDefinition"];

        $lng = $DIC->language();
        $lng->loadLanguageModule("rep");

        $tools = [];
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::SHOW_TREE_TOOL, true)) {

            $iff = function ($id) { return $this->identification_provider->identifier($id); };
            $l = function (string $content) { return $this->dic->ui()->factory()->legacy($content); };
            $ref_id = $called_contexts->current()->getReferenceId()->toInt();
            $type = ilObject::_lookupType((int) $ref_id, true);

            /*$mode = ($_SESSION["il_rep_mode"] != "")
                ? $_SESSION["il_rep_mode"]
                : "flat";
            $mode = "tree";*/

            if (
                (strtolower($_GET["baseClass"]) != "iladministrationgui") &&
                $objDefinition->isContainer($type)) {
                $tools[] = $this->factory->tool($iff("tree"))
                    ->withTitle($lng->txt("tree"))
                    ->withContent($l($this->getTree($ref_id)));
            }
        }

        return $tools;
    }


    /**
     * tree
     *
     * @param int $ref_id
     * @return string
     */
    private function getTree(int $ref_id) : string
    {
        global $DIC;
        
        /** @var ilObjectDefinition $objDefinition */
        $objDefinition = $DIC["objDefinition"];
        
        try {

            $type = ilObject::_lookupType((int) $_GET["ref_id"], true);

            $classname = "ilObj" . $objDefinition->getClassName($type) . "GUI";

            $mode = "tree";

            // check for administration context, see #0016312
            $exp = new ilRepositoryExplorerGUI($classname, "showRepTree");
            /*
            if(method_exists($this, 'getAdditionalWhitelistTypes')) {
                $whitelist = array_merge (
                    $exp->getTypeWhiteList(),
                    $this->getAdditionalWhitelistTypes()
                );
                $exp->setTypeWhiteList($whitelist);
            }*/

            return $exp->getHTML();
        } catch (Exception $e) {
            return "";
        }
    }
}
