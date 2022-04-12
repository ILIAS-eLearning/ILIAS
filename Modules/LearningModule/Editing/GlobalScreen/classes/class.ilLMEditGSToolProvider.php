<?php

use ILIAS\GlobalScreen\Scope\Tool\Provider\AbstractDynamicToolProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\Hasher;
use ILIAS\UI\Implementation\Component\MainControls\Slate\Legacy as LegacySlate;

/**
 * Learning module editing GS tool provider
 *
 * @author Alex Killing <killing@leifos.com>
 */
class ilLMEditGSToolProvider extends AbstractDynamicToolProvider
{
    const SHOW_TREE = 'show_tree';
    use Hasher;

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
            $title = $this->dic->language()->txt('objs_st');
            $icon = $this->dic->ui()->factory()->symbol()->icon()->custom(\ilUtil::getImagePath("outlined/icon_chp.svg"), $title);

            $iff = function ($id) {
                return $this->identification_provider->contextAwareIdentifier($id);
            };
            $l = function (string $content) {
                return $this->dic->ui()->factory()->legacy($content);
            };
            $identification = $iff("tree");
            $hashed = $this->hash($identification->serialize());
            $tools[] = $this->factory->tool($identification)
                ->addComponentDecorator(static function (ILIAS\UI\Component\Component $c) use ($hashed) : ILIAS\UI\Component\Component {
                    if ($c instanceof LegacySlate) {
                        $signal_id = $c->getToggleSignal()->getId();
                        return $c->withAdditionalOnLoadCode(static function ($id) use ($hashed) {
                            return "
                                 console.log('trigger added');
                                 $('body').on('il-lm-editor-tree', function(){
                                    il.UI.maincontrols.mainbar.engageTool('$hashed');
                                    console.log('trigger tree');
                                 });";
                        });
                    }
                    return $c;
                })
                ->withTitle($title)
                ->withSymbol($icon)
                ->withContentWrapper(function () use ($l) {
                    return $l($this->getContent());
                });
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
