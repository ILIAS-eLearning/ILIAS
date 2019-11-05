<?php

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Taxonomy GS tool provider
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilTaxonomyGSToolProvider extends AbstractDynamicToolProvider
{

    const SHOW_TAX_TREE = 'show_tax_tree';
    const TAX_TREE_GUI_PATH = 'tax_tree_gui_path';
    const TAX_ID = 'tax_id';
    const TAX_TREE_CMD = 'tax_tree_cmd';
    const TAX_TREE_PARENT_CMD = 'tax_tree_parent_cmd';


    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main()->main();
    }


    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        global $DIC;

        $lng = $DIC->language();
        $lng->loadLanguageModule("tax");

        $tools = [];
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::SHOW_TAX_TREE, true)) {

            $iff = function ($id) { return $this->identification_provider->identifier($id); };
            $l = function (string $content) { return $this->dic->ui()->factory()->legacy($content); };
            $tools[] = $this->factory->tool($iff("tree"))
                ->withTitle($lng->txt("tax_taxonomy"))
                ->withContent($l($this->getEditTree(
                    $additional_data->get(self::TAX_TREE_GUI_PATH),
                    $additional_data->get(self::TAX_ID),
                    $additional_data->get(self::TAX_TREE_CMD),
                    $additional_data->get(self::TAX_TREE_PARENT_CMD)
                    ))
                );
        }

        return $tools;
    }


    /**
     * @param $gui_path
     * @param $tax_id
     * @return string
     */
    private function getEditTree($gui_path, $tax_id, $cmd, $parent_cmd) : string
    {
        $target_gui = $gui_path[count($gui_path)-1];
        $tax_exp = new ilTaxonomyExplorerGUI($gui_path, $parent_cmd, $tax_id,
            $target_gui, $cmd);
        return $tax_exp->getHTML();
    }
}
