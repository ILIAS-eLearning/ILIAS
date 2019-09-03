<?php

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Scorm editor GS tool provider
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilSAHSEditGSToolProvider extends AbstractDynamicToolProvider
{

    const SHOW_SCORM_EDIT_TREE = 'show_scorm_edit_tree';


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
        if ($additional_data->is(self::SHOW_SCORM_EDIT_TREE, true)) {

            $iff = function ($id) { return $this->identification_provider->identifier($id); };
            $l = function (string $content) { return $this->dic->ui()->factory()->legacy($content); };
            $tools[] = $this->factory->tool($iff("tree"))
                ->withTitle("Organisation")
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
        $service = new ilSAHSEditService($_GET);
        $lm = $service->getLearningModule();
        $exp = new ilSCORM2004EditorExplorerGUI(["ilSAHSEditGUI", "ilObjSCORM2004LearningModuleGUI"], "showEditTree", $lm);
        return $exp->getHTML();
    }
}
