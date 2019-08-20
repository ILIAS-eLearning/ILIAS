<?php

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Class ilStaffGSToolProvider
 *
 * @author Alex Killing <killing@leifos.com>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMediaPoolGSToolProvider extends AbstractDynamicToolProvider
{

    const SHOW_FOLDERS_TOOL = 'show_folders_tool';


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
        $tools = [];
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::SHOW_FOLDERS_TOOL, true)) {

            $iff = function ($id) { return $this->identification_provider->identifier($id); };
            $l = function (string $content) { return $this->dic->ui()->factory()->legacy($content); };
            $ref_id = $called_contexts->current()->getReferenceId()->toInt();
            $tools[] = $this->factory->tool($iff("tree"))
                ->withTitle("Folders")
                ->withContent($l($this->getTree($ref_id)));
        }

        return $tools;
    }


    /**
     * @param int $ref_id
     *
     * @return string
     */
    private function getTree(int $ref_id) : string
    {
        try {
            $pool = ilObjectFactory::getInstanceByRefId($ref_id);
            $pool_gui = new ilObjMediaPoolGUI($ref_id);
            $exp = new ilMediaPoolExplorerGUI($pool_gui, "listMedia", $pool);

            return $exp->getHTML(true);
        } catch (Exception $e) {
            return "";
        }
    }
}
