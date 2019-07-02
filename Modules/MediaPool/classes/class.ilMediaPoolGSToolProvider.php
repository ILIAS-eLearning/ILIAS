<?php

use ILIAS\GlobalScreen\Scope\Tool\Context\Stack\CalledContexts;
use ILIAS\GlobalScreen\Scope\Tool\Context\Stack\ContextCollection;
use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;

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
        return $this->context_collection->main()->repository()->administration();
    }


    /**
     * @inheritDoc
     */
    public function getToolsForContextStack(CalledContexts $called_contexts) : array
    {
        $tools = [];
        $iff = function ($id) { return $this->identification_provider->identifier($id); };
        $l = function (string $content) { return $this->dic->ui()->factory()->legacy($content); };

        $additional_data = $called_contexts->getLast()->getAdditionalData();
        if ($additional_data->exists(self::SHOW_FOLDERS_TOOL) && $additional_data->get(self::SHOW_FOLDERS_TOOL) === true) {
            $tools[] = $this->factory->tool($iff("tree"))
                ->withTitle("Folders")
                ->withContent($l($this->getTree()));
        }

        return $tools;
    }


    /**
     * @return string
     */
    private function getTree() : string
    {
        $pool = ilObjectFactory::getInstanceByRefId((int) $_GET["ref_id"]);
        $pool_gui = new ilObjMediaPoolGUI((int) $_GET["ref_id"]);
        $exp = new ilMediaPoolExplorerGUI($pool_gui, "listMedia", $pool);

        return $exp->getHTML(true);
    }
}
