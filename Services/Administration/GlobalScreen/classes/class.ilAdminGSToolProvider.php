<?php

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Administration GS tool provider
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilAdminGSToolProvider extends AbstractDynamicToolProvider
{

    const SHOW_ADMIN_TREE = 'show_admin_tree';


    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main()->administration();
    }


    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        $tools = [];
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::SHOW_ADMIN_TREE, true)) {

            $iff = function ($id) { return $this->identification_provider->identifier($id); };
            $l = function (string $content) { return $this->dic->ui()->factory()->legacy($content); };
            $tools[] = $this->factory->tool($iff("tree"))
                ->withTitle("Tree")
                ->withContent($l($this->getTree()));
        }

        return $tools;
    }


    /**
     * @param int $ref_id
     *
     * @return string
     */
    private function getTree() : string
    {
        $exp = new ilAdministrationExplorerGUI("ilAdministrationGUI", "showTree");
        return $exp->getHTML();
    }
}
