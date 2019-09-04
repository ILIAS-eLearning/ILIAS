<?php

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Learning module editing GS tool provider
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilLMEditGSToolProvider extends AbstractDynamicToolProvider
{

    const SHOW_TREE = 'show_tree';


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
        if ($additional_data->is(self::SHOW_TREE, true)) {

            $iff = function ($id) { return $this->identification_provider->identifier($id); };
            $l = function (string $content) { return $this->dic->ui()->factory()->legacy($content); };
            $tools[] = $this->factory->tool($iff("tree"))
                ->withTitle("Chapters")
                ->withContent($l($this->getContent()));
        }

        return $tools;
    }


    /**
     * @param int $ref_id
     *
     * @return string
     */
    private function getContent() : string
    {
        $service = new ilLMEditService($_GET);
        $lm = $service->getLearningModule();

        $exp = new ilLMEditorExplorerGUI("illmeditorgui", "showTree", $lm);
        return $exp->getHTML();
    }
}
