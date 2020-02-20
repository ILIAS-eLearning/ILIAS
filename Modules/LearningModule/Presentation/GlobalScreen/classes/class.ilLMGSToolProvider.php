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
    use \ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;

    const SHOW_TOC_TOOL = 'show_toc_tool';
    const LM_QUERY_PARAMS = 'lm_query_params';
    const LM_OFFLINE = 'lm_offline';


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
            $iff = function ($id) {
                return $this->identification_provider->contextAwareIdentifier($id);
            };
            $l = function (string $content) {
                return $this->dic->ui()->factory()->legacy($content);
            };
            $ref_id = $called_contexts->current()->getReferenceId()->toInt();

            $tools[] = $this->getTocTool($additional_data);

            $title = $lng->txt("obj_glo");
            $icon = $DIC->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_glo.svg"),$title);
            $tools[] = $this->factory->tool($iff("lm_glossary"))
                ->withTitle($title)
                ->withContentWrapper(function () use ($l) {
                    return $l($this->getLinkSlateContent("glossary"));
                })
                ->withSymbol($icon)
                ->withPosition(11);

            $title = $lng->txt("cont_tool_media");
            $icon = $DIC->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_mdia.svg"),$title);
            $tools[] = $this->factory->tool($iff("lm_media"))
                ->withTitle($title)
                ->withContentWrapper(function () use ($l) {
                    return $l($this->getLinkSlateContent("media"));
                })
                ->withSymbol($icon)
                ->withPosition(12);

            $title = $lng->txt("cont_tool_media");
            $icon = $DIC->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_faq.svg"),$title);
            $tools[] = $this->factory->tool($iff("lm_faq"))
                ->withTitle($lng->txt("cont_tool_faq"))
                ->withContentWrapper(function () use ($l) {
                    return $l($this->getLinkSlateContent("faq"));
                })
                ->withSymbol($icon)
                ->withPosition(13);
        }

        return $tools;
    }

    /**
     *
     *
     * @param
     * @return
     */
    public function getOfflineToolIds()
    {
        $iff = function ($id) {
            return $this->identification_provider->contextAwareIdentifier($id);
        };
        return [
            $this->hash($iff("lm_pres_toc")->serialize()),
            $this->hash($iff("lm_glossary")->serialize()),
            $this->hash($iff("lm_media")->serialize()),
            $this->hash($iff("lm_faq")->serialize())
        ];
    }


    /**
     * Get toc tool
     *
     * @param
     * @return
     */
    public function getTocTool($additional_data) : \ILIAS\GlobalScreen\Scope\Tool\Factory\Tool
    {
        global $DIC;

        $lng = $DIC->language();

        $iff = function ($id) {
            return $this->identification_provider->contextAwareIdentifier($id);
        };
        $l = function (string $content) {
            return $this->dic->ui()->factory()->legacy($content);
        };

        $title = $lng->txt("cont_toc");
        $icon = $DIC->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_chp.svg"),$title);
        return $this->factory->tool($iff("lm_pres_toc"))
            ->withTitle($title)
            ->withContentWrapper(function () use ($l, $additional_data) {
                return $l($this->getToc($additional_data));
            })
            ->withSymbol($icon)
            ->withPosition(10);
    }


    /**
     * toc
     *
     * @param int $ref_id
     *
     * @return string
     */
    private function getToc($additional_data) : string
    {
        global $DIC;

        // get params via additional_data, set query params
        $params = $additional_data->get(self::LM_QUERY_PARAMS);
        $offline = $additional_data->is(self::LM_OFFLINE, true);
        if (!is_array($params)) {
            $params = $_GET;
        }

        try {
            $service = new ilLMPresentationService($DIC->user(), $params, $offline);
            $renderer = new ilLMSlateTocRendererGUI($service);

            return $renderer->render();
        } catch (Exception $e) {
            return "";
        }
    }


    /**
     * @param string
     *
     * @return string
     */
    protected function getLinkSlateContent(string $type) : string
    {
        return "<div style='height:100%; overflow:hidden;' id='" . $type . "_area'><iframe style='border:0; padding:0; height:100%; width:100%'></iframe></div>";
    }
}
