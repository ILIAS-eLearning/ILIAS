<?php

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Workspace GS tool provider
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilWorkspaceGSToolProvider extends AbstractDynamicToolProvider
{

    const SHOW_WS_TREE = 'show_ws_tree';


    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main()->desktop();
    }


    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        $tools = [];
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::SHOW_WS_TREE, true)) {

            $iff = function ($id) { return $this->identification_provider->identifier($id); };
            $l = function (string $content) { return $this->dic->ui()->factory()->legacy($content); };
            $ref_id = $called_contexts->current()->getReferenceId()->toInt();
            $tools[] = $this->factory->tool($iff("tree"))
                ->withTitle("Folders")
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
        global $DIC;

        $user = $DIC->user();
        $exp = new ilWorkspaceExplorerGUI($user->getId(), ["ilPersonalWorkspaceGUI", "ilObjWorkspaceFolderGUI"], "render", "ilObjWorkspaceFolderGUI", "", "wsp_id");
        $exp->setTypeWhiteList(array("wsrt", "wfld"));
        $exp->setSelectableTypes(array("wsrt", "wfld"));
        $exp->setLinkToNodeClass(true);
        $exp->setAjax(false);
        $exp->setActivateHighlighting(true);
        return $exp->getHTML(true);
    }
}
