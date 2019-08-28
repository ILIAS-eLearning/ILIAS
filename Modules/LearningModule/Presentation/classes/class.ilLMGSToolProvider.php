<?php

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Class ilLMGSToolProvider
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilLMGSToolProvider extends AbstractDynamicToolProvider
{

    const SHOW_TOC_TOOL = 'show_toc_tool';


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

        $lng = $DIC->language();
        $lng->loadLanguageModule("content");

        $tools = [];
        $additional_data = $called_contexts->current()->getAdditionalData();
        if ($additional_data->is(self::SHOW_TOC_TOOL, true)) {

            $iff = function ($id) { return $this->identification_provider->identifier($id); };
            $l = function (string $content) { return $this->dic->ui()->factory()->legacy($content); };
            $ref_id = $called_contexts->current()->getReferenceId()->toInt();

            $tools[] = $this->factory->tool($iff("toc"))
                ->withTitle($lng->txt("cont_toc"))
                ->withContent($l($this->getToc($ref_id)));

            $tools[] = $this->factory->tool($iff("glossary"))
                ->withTitle($lng->txt("obj_glo"))
                ->withContent($l($this->getGlossary($ref_id)));

            $tools[] = $this->factory->tool($iff("media"))
                ->withTitle($lng->txt("cont_media"))
                ->withContent($l($this->getMedia($ref_id)));
        }

        return $tools;
    }


    /**
     * toc
     *
     * @param int $ref_id
     * @return string
     */
    private function getToc(int $ref_id) : string
    {
        try {
            $renderer = new ilLMSlateTocRendererGUI();
            return $renderer->render();
        } catch (Exception $e) {
            return "";
        }
    }

    /**
     * glossary
     *
     * @param int $ref_id
     * @return string
     */
    protected function getGlossary(int $ref_id) : string
    {
        return "Glossary";
    }

    /**
     * media
     *
     * @param int $ref_id
     * @return string
     */
    protected function getMedia(int $ref_id) : string
    {
        return "Media";
    }

}
