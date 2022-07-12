<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Implementation\Component\MainControls\Slate\Legacy as LegacySlate;
use ILIAS\GlobalScreen\ScreenContext\AdditionalData\Collection;
use ILIAS\GlobalScreen\Scope\Tool\Factory\Tool;

/**
 * @author Alexander Killing <killing@leifos.de>
 */
class ilLMGSToolProvider extends AbstractDynamicToolProvider
{
    use \ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;

    public const SHOW_TOC_TOOL = 'show_toc_tool';
    public const SHOW_LINK_SLATES = 'show_link_slates';
    public const LM_QUERY_PARAMS = 'lm_query_params';
    public const LM_OFFLINE = 'lm_offline';

    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main()->repository();
    }

    public function getToolsForContextStack(
        CalledContexts $called_contexts
    ) : array {
        global $DIC;
        $lng = $DIC->language();
        $access = $DIC->access();
        
        $lng->loadLanguageModule("content");

        $tools = [];
        $additional_data = $called_contexts->current()->getAdditionalData();
        $iff = function ($id) {
            return $this->identification_provider->contextAwareIdentifier($id);
        };
        $l = function (string $content) {
            return $this->dic->ui()->factory()->legacy($content);
        };

        if ($additional_data->is(self::SHOW_TOC_TOOL, true)) {
            $ref_id = $called_contexts->current()->getReferenceId()->toInt();

            if (!$access->checkAccess("read", "", $ref_id)) {
                return $tools;
            }

            $tools[] = $this->getTocTool($additional_data);
        }

        if ($additional_data->is(self::SHOW_LINK_SLATES, true)) {
            $title = $lng->txt("obj_glo");
            $icon = $DIC->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("icon_glo.svg"), $title);
            $identification = $iff("lm_glossary");
            $hashed = $this->hash($identification->serialize());
            $tools[] = $this->factory->tool($identification)
                ->addComponentDecorator(static function (ILIAS\UI\Component\Component $c) use ($hashed) : ILIAS\UI\Component\Component {
                    if ($c instanceof LegacySlate) {
                        $signal_id = $c->getToggleSignal()->getId();
                        return $c->withAdditionalOnLoadCode(static function ($id) use ($hashed) {
                            return "
                                                 $('body').on('il-lm-show-glossary-slate', function(){
                                                     il.UI.maincontrols.mainbar.engageTool('$hashed');
                                                 });";
                        });
                    }
                    return $c;
                })
                ->withInitiallyHidden(true)
                ->withTitle($title)
                ->withContentWrapper(function () use ($l) {
                    return $l($this->getLinkSlateContent("glossary"));
                })
                ->withSymbol($icon)
                ->withPosition(11);

            $title = $lng->txt("cont_tool_media");
            $icon = $DIC->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("icon_mdia.svg"), $title);
            $identification = $iff("lm_media");
            $hashed = $this->hash($identification->serialize());
            $tools[] = $this->factory->tool($identification)
                ->addComponentDecorator(static function (ILIAS\UI\Component\Component $c) use ($hashed) : ILIAS\UI\Component\Component {
                    if ($c instanceof LegacySlate) {
                        $signal_id = $c->getToggleSignal()->getId();
                        return $c->withAdditionalOnLoadCode(static function ($id) use ($hashed) {
                            return "
                                                 $('body').on('il-lm-show-media-slate', function(){
                                                     il.UI.maincontrols.mainbar.engageTool('$hashed');
                                                 });";
                        });
                    }
                    return $c;
                })
                ->withInitiallyHidden(true)
                ->withTitle($title)
                ->withContentWrapper(function () use ($l) {
                    return $l($this->getLinkSlateContent("media"));
                })
                ->withSymbol($icon)
                ->withPosition(12);

            $title = $lng->txt("cont_tool_faq");
            $icon = $DIC->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("icon_faq.svg"), $title);
            $identification = $iff("lm_faq");
            $hashed = $this->hash($identification->serialize());
            $tools[] = $this->factory->tool($identification)
                ->addComponentDecorator(static function (ILIAS\UI\Component\Component $c) use ($hashed) : ILIAS\UI\Component\Component {
                    if ($c instanceof LegacySlate) {
                        $signal_id = $c->getToggleSignal()->getId();
                        return $c->withAdditionalOnLoadCode(static function ($id) use ($hashed) {
                            return "
                                                 $('body').on('il-lm-show-faq-slate', function(){
                                                     il.UI.maincontrols.mainbar.engageTool('$hashed');
                                                 });";
                        });
                    }
                    return $c;
                })
                ->withInitiallyHidden(true)
                ->withTitle($title)
                ->withContentWrapper(function () use ($l) {
                    return $l($this->getLinkSlateContent("faq"));
                })
                ->withSymbol($icon)
                ->withPosition(13);
        }
        return $tools;
    }

    public function getOfflineToolIds() : array
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

    public function getTocTool(
        Collection $additional_data
    ) : Tool {
        global $DIC;

        $lng = $DIC->language();

        $iff = function ($id) {
            return $this->identification_provider->contextAwareIdentifier($id);
        };
        $l = function (string $content) {
            return $this->dic->ui()->factory()->legacy($content);
        };

        $title = $lng->txt("cont_toc");
        $icon = $DIC->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("icon_chp.svg"), $title);
        return $this->factory->tool($iff("lm_pres_toc"))
            ->withTitle($title)
            ->withContentWrapper(function () use ($l, $additional_data) {
                return $l($this->getToc($additional_data));
            })
            ->withSymbol($icon)
            ->withPosition(10);
    }


    private function getToc(Collection $additional_data) : string
    {
        global $DIC;

        // get params via additional_data, set query params
        $params = null;
        if ($additional_data->exists(self::LM_QUERY_PARAMS)) {
            $params = $additional_data->get(self::LM_QUERY_PARAMS);
        }
        $offline = $additional_data->is(self::LM_OFFLINE, true);

        if (!is_array($params)) {
            $params = null;
        }
        //try {
        $service = new ilLMPresentationService($DIC->user(), $params, $offline);
        $renderer = new ilLMSlateTocRendererGUI($service);

        return $renderer->render();
        //} catch (Exception $e) {
        //    return $e->getMessage();
        //}
    }

    protected function getLinkSlateContent(string $type) : string
    {
        return "<div style='height:100%; overflow:hidden;' id='" . $type . "_area'><iframe style='border:0; padding:0; height:100%; width:100%'></iframe></div>";
    }
}
